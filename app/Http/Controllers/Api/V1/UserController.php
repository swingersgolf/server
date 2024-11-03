<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserUpdateRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show()
    {
        return new UserResource(User::find(Auth::id()));
    }

    public function update(UserUpdateRequest $request) 
    {
        $user = User::find(Auth::id());
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        $user->update($request->only(['expo_push_token']));
        return new UserResource($user);
    }
}
