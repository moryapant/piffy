<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\Subfapp;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Allow admins to do everything
        if ($user->is_admin == 1) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can create a post in the given subfapp.
     */
    public function create(User $user, Subfapp $subfapp): bool
    {
        return $subfapp->canPost($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // Check if user is admin or post owner
        return $user->is_admin == 1 || $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // Check if user is admin or post owner
        return $user->is_admin == 1 || $user->id === $post->user_id;
    }
}
