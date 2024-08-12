<?php
namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserProfileRepositoryInterface;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function update(string $userId, array $attributes)
    {
        $user = User::find($userId);
        $user->updateFillableAttributesOnly($attributes);
        $user->userProfile->updateFillableAttributesOnly($attributes);
    }
}
