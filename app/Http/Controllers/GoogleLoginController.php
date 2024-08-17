<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    use ApiResponses;

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::where('email', $googleUser->email)->first();
        if (! $user) {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => Hash::make(Str::random(16)),
            ]);
        }

        return $this->ok('Authenticated',
            [
                'token' => $user->createToken('API Token for Google user '.$user->email)->plainTextToken,
            ]);
    }
}
