<?php
namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function update(string $userId, array $attributes)
    {
        $user = User::find($userId);
        $user->updateFillableAttributesOnly($attributes);
        $user->userProfile->updateFillableAttributesOnly($attributes);
    }
}
