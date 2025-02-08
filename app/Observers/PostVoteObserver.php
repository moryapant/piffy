<?php

namespace App\Observers;

use App\Models\PostVote;
use App\Services\PostSorting;

class PostVoteObserver
{
    protected $postSorting;

    public function __construct(PostSorting $postSorting)
    {
        $this->postSorting = $postSorting;
    }

    /**
     * Handle the PostVote "created" event.
     */
    public function created(PostVote $postVote): void
    {
        $this->updatePostScores($postVote);
    }

    /**
     * Handle the PostVote "updated" event.
     */
    public function updated(PostVote $postVote): void
    {
        $this->updatePostScores($postVote);
    }

    /**
     * Handle the PostVote "deleted" event.
     */
    public function deleted(PostVote $postVote): void
    {
        $this->updatePostScores($postVote);
    }

    /**
     * Update post scores when votes change
     */
    protected function updatePostScores(PostVote $postVote): void
    {
        $post = $postVote->post;
        
        // Update hot score
        $this->postSorting->updateHotScore($post);
        
        // Check if post is trending
        $this->postSorting->updateTrendingStatus($post);
    }
}
