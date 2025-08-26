<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostVote;
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
            'vote_type' => 'required|in:1,-1'
        ]);

        $voteType = $request->input('vote_type');
        
        $vote = PostVote::updateOrCreate(
            ['user_id' => auth()->id(), 'post_id' => $post->id],
            ['vote_type' => $voteType]
        );

        // Update post vote counts
        $upvotes = $post->votes()->where('vote_type', 1)->count();
        $downvotes = $post->votes()->where('vote_type', -1)->count();

        $post->update([
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
        ]);

        // Record the vote action in visits table for activity tracking
        \DB::table('visits')->insert([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?? 'Unknown',
            'page_visited' => $request->fullUrl(),
            'page_title' => "Post Vote: " . $post->title,
            'user_id' => auth()->id(),
            'activity_type' => 'post_vote',
            'model_id' => $post->id,
            'model_type' => 'Post',
            'activity_data' => json_encode(['vote_type' => $voteType]),
            'visited_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back();
    }
}
