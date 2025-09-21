<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostVote;
use App\Services\NotificationService;
use App\Services\VisitService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PostVoteController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function vote(Post $post, Request $request)
    {
        $request->validate([
            'vote_type' => 'required|in:1,-1,0',
        ]);

        $voteType = $request->input('vote_type');

        $wasVoted = PostVote::where('user_id', auth()->id())
            ->where('post_id', $post->id)
            ->exists();

        if ($voteType == 0) {
            // Remove the vote if vote_type is 0
            PostVote::where('user_id', auth()->id())
                ->where('post_id', $post->id)
                ->delete();
        } else {
            // Create or update the vote
            $vote = PostVote::updateOrCreate(
                ['user_id' => auth()->id(), 'post_id' => $post->id],
                ['vote_type' => $voteType]
            );

            // Create notification for new votes (not updates)
            if (! $wasVoted && $voteType != 0) {
                $this->notificationService->createVoteNotification(
                    $post,
                    auth()->user(),
                    $voteType
                );
            }
        }

        // Update post vote counts
        $upvotes = $post->votes()->where('vote_type', 1)->count();
        $downvotes = $post->votes()->where('vote_type', -1)->count();
        $score = $upvotes - $downvotes;

        $post->update([
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
            'score' => $score,
        ]);

        // Record the vote action in visits table using the service
        VisitService::recordActivity(
            $request,
            'post_vote',
            $voteType == 0 ? 'Post Vote Removed: '.$post->title : 'Post Vote: '.$post->title,
            $post->id,
            'Post',
            ['vote_type' => $voteType]
        );

        // Get the user's current vote for this post
        $userVote = PostVote::where('user_id', auth()->id())
            ->where('post_id', $post->id)
            ->first();

        // If it's an AJAX request (from the voting buttons), return JSON with updated data
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'post_id' => $post->id,
                'upvotes' => $upvotes,
                'downvotes' => $downvotes,
                'score' => $score,
                'user_vote' => $userVote ? [
                    'vote_type' => $userVote->vote_type,
                ] : null,
            ]);
        }

        return back();
    }
}
