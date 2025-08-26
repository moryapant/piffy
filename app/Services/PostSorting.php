<?php

namespace App\Services;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class PostSorting
{
    /**
     * Hot: Most views in last few hours
     */
    public function hot(Builder $query): Builder
    {
        $threshold = Carbon::now()->subHours(6); // Last 6 hours

        return $query
            ->withCount(['views' => function ($query) use ($threshold) {
                $query->where('created_at', '>=', $threshold);
            }])
            ->orderBy('views_count', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Trending: Posts with high activity in recent hours
     * Combines views, votes, and comments with time decay
     */
    public function trending(Builder $query): Builder
    {
        return $query
            ->selectRaw('
                posts.*, 
                (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id AND comments.deleted_at IS NULL) as comments_count,
                COALESCE(posts.score, 0) + 
                COALESCE(posts.views_count, 0) / 10 + 
                (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id AND comments.deleted_at IS NULL) * 2 +
                CASE 
                    WHEN posts.created_at >= ? THEN 50
                    WHEN posts.created_at >= ? THEN 25  
                    WHEN posts.created_at >= ? THEN 10
                    ELSE 0 
                END as trending_score
            ', [
                Carbon::now()->subHour(),      // Posts from last hour get +50
                Carbon::now()->subHours(3),    // Posts from last 3 hours get +25
                Carbon::now()->subHours(6),    // Posts from last 6 hours get +10
            ])
            ->where('posts.created_at', '>=', Carbon::now()->subHours(24)) // Only consider posts from last 24 hours
            ->orderBy('trending_score', 'desc')
            ->orderBy('posts.created_at', 'desc');
    }

    /**
     * New: Latest posts
     */
    public function new(Builder $query): Builder
    {
        return $query->latest();
    }

    /**
     * Top: Most likes in last few hours
     */
    public function top(Builder $query): Builder
    {
        $threshold = Carbon::now()->subHours(6); // Last 6 hours

        return $query
            ->withCount(['votes as recent_upvotes' => function ($query) use ($threshold) {
                $query->where('vote_type', 1)
                    ->where('created_at', '>=', $threshold);
            }])
            ->orderBy('recent_upvotes', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Rising: Most commented in last few hours
     */
    public function rising(Builder $query): Builder
    {
        $threshold = Carbon::now()->subHours(6); // Last 6 hours

        return $query
            ->withCount(['comments as recent_comments_count' => function ($query) use ($threshold) {
                $query->where('created_at', '>=', $threshold);
            }])
            ->having('recent_comments_count', '>', 0)
            ->orderBy('recent_comments_count', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public function updateTrendingStatus(Post $post): void
    {
        // Calculate activity in last 3 hours
        $recentViews = $post->views_count - ($post->views_count_24h ?? 0);
        $recentScore = $post->score - ($post->score_24h ?? 0);

        // Post is trending if it has significant recent activity
        if ($recentViews >= 50 || $recentScore >= 10) {
            if (! $post->trending_start) {
                $post->trending_start = Carbon::now();
                $post->save();
            }
        } else {
            // Remove trending status if activity drops
            if ($post->trending_start && $post->trending_start <= Carbon::now()->subHours(24)) {
                $post->trending_start = null;
                $post->save();
            }
        }
    }

    public function updateHotScore(Post $post): void
    {
        // Calculate vote score from actual vote records
        $upvotes = $post->votes()->where('vote_type', 1)->count();
        $downvotes = $post->votes()->where('vote_type', -1)->count();
        $score = $upvotes - $downvotes;

        $post->score = $score;

        // Calculate hot score using Reddit's algorithm
        $order = log10(max(abs($score), 1));
        $sign = $score > 0 ? 1 : ($score < 0 ? -1 : 0);
        $seconds = $post->created_at->timestamp - 1134028003; // Reddit's epoch
        $post->hot_score = round($sign * $order + $seconds / 45000, 7);

        // Update trending status if score is high
        if ($score >= 5 && ! $post->trending_start) { // Lower threshold for testing
            $post->trending_start = Carbon::now();
        }

        $post->save();
    }
}
