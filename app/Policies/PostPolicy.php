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
        $isOwner = $user->id === $post->user_id;
        $isAdmin = $user->is_admin == 1 || $user->is_admin === true;
        $result = $isOwner || $isAdmin;

        // Debug logging
        \Log::info('PostPolicy::update debug', [
            'user_id' => $user->id,
            'post_user_id' => $post->user_id,
            'is_admin' => $user->is_admin,
            'is_owner' => $isOwner,
            'is_admin_explicit' => $isAdmin,
            'result' => $result
        ]);

        return $result;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->is_admin == 1 || $user->is_admin === true;
    }
}
