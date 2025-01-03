<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\LoginUserRequest;
use App\Http\Requests\Api\V1\RegisterUserRequest;
use App\Http\Requests\Api\V1\ResendVerificationEmailRequest;
use App\Http\Requests\Api\V1\ResetPasswordRequest;
use App\Http\Requests\Api\V1\VerifyEmailRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthController extends Controller
{
    use ApiResponses;

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        if (! Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Invalid Credentials', 401);
        }

        $user = User::firstWhere('email', $request->email);

        if (empty($user->email_verified_at)) {
            return $this->error('Expired Credentials', ResponseAlias::HTTP_PRECONDITION_REQUIRED);
        }

        return $this->ok('Authenticated',
            [
                'token' => $user->createToken('API Token for '.$user->email)->plainTextToken,
            ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->tokens()->delete();

            return $this->success('Logged out successfully.', []);
        }

        return $this->error('Unable to log out.', 400);
    }

    public function register(RegisterUserRequest $request): JsonResponse
    {
        $request->validated($request->all());

        $user = User::create($request->all());

        $email = $request->email;
        $code = random_int(100000, 999999);
        $expiryMinutes = 30;
        $expiration = now()->addMinutes($expiryMinutes);

        Cache::put("verification_code_{$email}", [
            'code' => strval($code),
            'expires_at' => $expiration,
        ], $expiration);

        $user->notify(new VerifyEmailNotification($code));

        return $this->success('User created', [], ResponseAlias::HTTP_CREATED);
    }

    public function resend(ResendVerificationEmailRequest $request): JsonResponse
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();

        $code = random_int(100000, 999999);
        $expiryMinutes = 30;
        $expiration = now()->addMinutes($expiryMinutes);

        Cache::put("verification_code_{$email}", [
            'code' => strval($code),
            'expires_at' => $expiration,
        ], $expiration);

        $user->notify(new VerifyEmailNotification($code));

        return $this->success('Verification Email Resent', [], ResponseAlias::HTTP_OK);
    }

    public function verify(VerifyEmailRequest $request)
    {
        $email = $request->email;
        $code = strval($request->code);
        $cachedData = Cache::get("verification_code_{$email}");

        $user = User::where('email', $email)->first();

        if (! ($cachedData && strval($cachedData['code']) === $code)) {
            return $this->error('Invalid Code', ResponseAlias::HTTP_PRECONDITION_REQUIRED);
        }

        if (! now()->lessThanOrEqualTo($cachedData['expires_at'])) {
            return $this->error('Expired Code', ResponseAlias::HTTP_PRECONDITION_REQUIRED);
        }

        Cache::forget("verification_code_{$email}");
        $user->markEmailAsVerified();

        return $this->ok('Code verified successfully.', []);

    }

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->firstOrFail();

        $email = $request->input('email');
        $code = random_int(100000, 999999);
        $expiryMinutes = 30;
        $expiration = now()->addMinutes($expiryMinutes);
        Cache::put("reset_code_{$email}", [
            'code' => strval($code),
            'expires_at' => $expiration,
        ], $expiration);

        $user->notify(new ResetPasswordNotification($code, 30));

        return $this->ok('Password reset link sent.');

    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $email = $request->email;
        $code = strval($request->code);
        $password = $request->password;

        $cachedData = Cache::get("reset_code_{$email}");

        if (! ($cachedData && strval($cachedData['code']) === $code)) {
            return $this->error('Invalid Code', ResponseAlias::HTTP_PRECONDITION_REQUIRED);
        }

        Cache::forget("reset_code_{$email}");
        $user = User::where('email', $email)->first();

        $user->password = Hash::make($password);

        $user->save();

        return $this->ok('Password reset successfully.');
    }
}
