<?php

namespace App\Policies;

use App\Models\MessageGroup;
use App\Models\User;

class MessageGroupPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MessageGroup $messageGroup): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, MessageGroup $messageGroup): bool
    {
        return $messageGroup->active && $messageGroup->users()
            ->where('user_id', $user->id)
            ->wherePivot('active', true)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MessageGroup $messageGroup): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MessageGroup $messageGroup): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MessageGroup $messageGroup): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MessageGroup $messageGroup): bool
    {
        return false;
    }
}
