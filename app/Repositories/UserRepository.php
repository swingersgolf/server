<?php
namespace App\Repositories;

use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function show(string $userId)
    {
        $user = User::find($userId);
        return new UserResource($user);
    }
    public function update(string $userId, array $attributes)
    {
        $user = User::find($userId);
        $user->updateFillableAttributesOnly($attributes);
        $user->userProfile->updateFillableAttributesOnly($attributes);
    }
}
