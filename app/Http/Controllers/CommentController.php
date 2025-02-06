<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = new Comment($validated);
        $comment->user_id = auth()->id();
        $comment->post_id = $post->id;
        $comment->save();

        return back()->with('success', 'Comment added successfully!');
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return back()->with('success', 'Comment deleted successfully!');
    }
}
