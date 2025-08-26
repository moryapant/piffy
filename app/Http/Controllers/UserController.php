<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function profile(User $user)
    {
        $posts = $user->posts()
            ->with(['user', 'subfapp', 'images'])
            ->select(['*', 'upvotes as upvotes_count', 'downvotes as downvotes_count'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('User/Profile', [
            'user' => $user,
            'posts' => $posts
        ]);
    }
}
