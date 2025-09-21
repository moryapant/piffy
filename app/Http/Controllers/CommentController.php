<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Services\NotificationService;
use App\Services\VisitService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CommentController extends Controller
{
    use AuthorizesRequests;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
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

        // Load the user relationship for notification
        $comment->load('user');

        // Create notification for post owner
        $this->notificationService->createCommentNotification($comment, $post);

        // Record the comment activity using the service
        VisitService::recordActivity(
            $request,
            'comment',
            'Comment on: '.$post->title,
            $comment->id,
            'Comment',
            ['comment_id' => $comment->id, 'post_id' => $post->id]
        );

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            // Load user votes if authenticated
            if (auth()->check()) {
                $comment->load(['votes' => function ($query) {
                    $query->where('user_id', auth()->id());
                }]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully!',
                'comment' => $comment->toArray(),
            ]);
        }

        return back()->with('success', 'Comment added successfully!');
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return back()->with('success', 'Comment deleted successfully!');
    }

    public function index(Request $request)
    {
        $query = Comment::with(['user', 'post'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('content', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('post_id')) {
            $query->where('post_id', $request->post_id);
        }

        $comments = $query->paginate(20);

        return inertia('Comments/Index', [
            'comments' => $comments,
            'filters' => $request->only(['search', 'user_id', 'post_id']),
        ]);
    }

    public function edit(Comment $comment)
    {
        $this->authorize('update', $comment);

        return inertia('Comments/Edit', [
            'comment' => $comment->load(['user', 'post']),
        ]);
    }

    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment->update($validated);

        // Record the update activity using the service
        VisitService::recordActivity(
            $request,
            'update_comment',
            'Updated comment on: '.$comment->post->title,
            $comment->id,
            'Comment',
            ['comment_id' => $comment->id, 'post_id' => $comment->post_id]
        );

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Comment updated successfully!',
                'comment' => $comment->toArray(),
            ]);
        }

        return redirect()->route('posts.show', $comment->post)
            ->with('success', 'Comment updated successfully!');
    }

    public function reply(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'required|exists:comments,id',
        ]);

        $comment = new Comment([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
            'post_id' => $post->id,
            'parent_id' => $validated['parent_id'],
        ]);

        $comment->save();

        // Load the user relationship for notification
        $comment->load('user');

        // Create notification for post owner (for replies too)
        $this->notificationService->createCommentNotification($comment, $post);

        // Record the reply activity using the service
        VisitService::recordActivity(
            $request,
            'reply',
            'Reply on: '.$post->title,
            $comment->id,
            'Comment',
            [
                'comment_id' => $comment->id,
                'post_id' => $post->id,
                'parent_id' => $validated['parent_id'],
            ]
        );

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            // Load user votes if authenticated
            if (auth()->check()) {
                $comment->load(['votes' => function ($query) {
                    $query->where('user_id', auth()->id());
                }]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Reply added successfully!',
                'comment' => $comment->toArray(),
            ]);
        }

        return back()->with('success', 'Reply added successfully!');
    }
}
