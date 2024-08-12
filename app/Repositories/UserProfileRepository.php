<?php
namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserProfileRepositoryInterface;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function update(string $userId, array $attributes)
    {
        $user = User::find($userId);
        $user->updateFillableAttributes($attributes);
        $user->userProfile->updateFillableAttributes($attributes);
    }
}
