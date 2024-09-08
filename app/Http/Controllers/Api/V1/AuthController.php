<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginUserRequest;
use App\Http\Requests\Api\V1\RegisterUserRequest;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
}
