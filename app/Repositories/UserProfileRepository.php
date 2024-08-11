<?php
namespace App\Repositories;

use App\Models\User;
use App\Models\UserProfile;
use App\Repositories\Interfaces\UserProfileRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function update(string $userId, array $attributes)
    {
        $user = User::find($userId);
        $user->userProfile()->update($attributes);
    }
}
