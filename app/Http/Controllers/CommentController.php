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

        // Record the comment activity in visits table
        \DB::table('visits')->insert([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?? 'Unknown',
            'page_visited' => $request->fullUrl(),
            'page_title' => "Comment on: " . $post->title,
            'user_id' => auth()->id(),
            'activity_type' => 'comment',
            'model_id' => $comment->id,
            'model_type' => 'Comment',
            'activity_data' => json_encode(['comment_id' => $comment->id, 'post_id' => $post->id]),
            'visited_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Comment added successfully!');
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return back()->with('success', 'Comment deleted successfully!');
    }

    public function reply(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'required|exists:comments,id'
        ]);

        $comment = new Comment([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
            'post_id' => $post->id,
            'parent_id' => $validated['parent_id']
        ]);

        $comment->save();

        // Record the reply activity in visits table
        \DB::table('visits')->insert([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?? 'Unknown',
            'page_visited' => $request->fullUrl(),
            'page_title' => "Reply on: " . $post->title,
            'user_id' => auth()->id(),
            'activity_type' => 'reply',
            'model_id' => $comment->id,
            'model_type' => 'Comment',
            'activity_data' => json_encode([
                'comment_id' => $comment->id, 
                'post_id' => $post->id, 
                'parent_id' => $validated['parent_id']
            ]),
            'visited_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Reply added successfully!');
    }
}
