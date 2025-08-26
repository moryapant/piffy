<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostVote;
use App\Services\VisitService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PostVoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function vote(Post $post, Request $request)
    {
        $request->validate([
            'vote_type' => 'required|in:1,-1,0',
        ]);

        $voteType = $request->input('vote_type');

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

        return back();
    }
}
