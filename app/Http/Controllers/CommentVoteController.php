<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentVote;
use App\Services\VisitService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CommentVoteController extends Controller
{
    public function vote(Request $request, Comment $comment)
    {
        $request->validate([
            'vote_type' => 'required|integer|in:-1,0,1',
        ]);

        $user = auth()->user();
        $voteType = $request->input('vote_type');

        // Find existing vote
        $existingVote = CommentVote::where('user_id', $user->id)
            ->where('comment_id', $comment->id)
            ->first();

        if ($voteType == 0) {
            // Remove vote
            if ($existingVote) {
                $this->updateCommentCounts($comment, $existingVote->vote_type, 0);
                $existingVote->delete();
            }
        } else {
            if ($existingVote) {
                // Update existing vote
                $oldVoteType = $existingVote->vote_type;
                $existingVote->update(['vote_type' => $voteType]);
                $this->updateCommentCounts($comment, $oldVoteType, $voteType);
            } else {
                // Create new vote
                CommentVote::create([
                    'user_id' => $user->id,
                    'comment_id' => $comment->id,
                    'vote_type' => $voteType,
                ]);
                $this->updateCommentCounts($comment, 0, $voteType);
            }
        }

        // Record activity
        VisitService::recordActivity(
            $request,
            'comment_vote',
            'Voted on comment: '.substr($comment->content, 0, 50),
            $comment->id,
            'Comment',
            [
                'comment_id' => $comment->id,
                'vote_type' => $voteType,
                'post_id' => $comment->post_id,
            ]
        );

        // Reload comment with updated counts
        $comment->refresh();

        // Load user vote for response
        $comment->load(['votes' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }]);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'upvotes' => $comment->upvotes,
                    'downvotes' => $comment->downvotes,
                    'score' => $comment->score,
                    'user_vote' => $comment->user_vote,
                ],
            ]);
        }

        return back();
    }

    private function updateCommentCounts(Comment $comment, int $oldVoteType, int $newVoteType)
    {
        // Remove old vote counts
        if ($oldVoteType == 1) {
            $comment->decrement('upvotes');
        } elseif ($oldVoteType == -1) {
            $comment->decrement('downvotes');
        }

        // Add new vote counts
        if ($newVoteType == 1) {
            $comment->increment('upvotes');
        } elseif ($newVoteType == -1) {
            $comment->increment('downvotes');
        }
    }
}
