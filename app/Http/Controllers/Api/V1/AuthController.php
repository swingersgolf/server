<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginUserRequest;
use App\Http\Requests\Api\V1\RegisterUserRequest;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
            return $this->error('Invalid Credentials', 401);
        }

        return $this->ok('Authenticated',
            [
                'token' => $user->createToken('API Token for '.$user->email)->plainTextToken,
            ]);
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
            'code' => $code,
            'expires_at' => $expiration
        ], $expiration);

        $user->notify(new VerifyEmailNotification($code,30));

        return $this->success('User created', [], 201);
    }

    public function verify(Request $request)
    {
        $email = $request->email;
        $code = $request->code;
        $cachedData = Cache::get("verification_code_{$email}");
        $user = User::where('email', $email)->first();

        if ($cachedData && $cachedData['code'] === $code && now()->lessThanOrEqualTo($cachedData['expires_at'])) {
            Cache::forget("verification_code_{$email}");
            $user->markEmailAsVerified();
            return $this->ok('Code verified successfully.',[]);
        }

        return $this->error('Invalid or expired code.', ResponseAlias::HTTP_BAD_REQUEST);
    }
}
