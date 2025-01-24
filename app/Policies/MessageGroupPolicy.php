<?php

namespace App\Policies;

use App\Models\MessageGroup;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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
        return $messageGroup->users()->where('users.id', $user->id)->exists();;
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
