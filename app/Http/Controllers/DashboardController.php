<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostVote;


class DashboardController extends Controller
{
    /**
     * Display the dashboard page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Load the relationships and get counts
        $stats = [
            'total_posts' => $user->posts()->count(),
            'total_comments' => $user->comments()->count(),
            'total_votes' => $user->votes()->count(),
            'upvotes_given' => $user->votes()->where('vote_type', 1)->count(),
            'downvotes_given' => $user->votes()->where('vote_type', -1)->count(),
        ];

        return Inertia::render('Dashboard', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'created_at' => $user->created_at->format('M d, Y'),
            ],
            'stats' => $stats,
        ]);
    }
}
