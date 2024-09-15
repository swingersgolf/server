<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserProfileUpdateRequest;
use App\Http\Resources\Api\V1\UserProfileResource;

class UserProfileController extends Controller
{
    public function show(): UserProfileResource
    {
        $user = auth()->user();

        return new UserProfileResource($user->userProfile);
    }

    public function update(UserProfileUpdateRequest $request): UserProfileResource
    {
        $user = auth()->user();
        $user->userProfile()->update($request->validated());

        return new UserProfileResource($user->userProfile);
    }
}
