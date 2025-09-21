<?php

namespace App\Services;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class PostSorting
{
    /**
     * Hot: Posts with high engagement using hot_score algorithm
     * Combines votes, comments, views with time decay
     */
    public function hot(Builder $query): Builder
    {
        return $query
            ->orderByDesc('hot_score')
            ->orderByDesc('created_at');
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

        // Get recent engagement metrics (last 6 hours)
        $recentComments = $post->comments()
            ->where('created_at', '>=', Carbon::now()->subHours(6))
            ->count();
        $recentViews = $post->views()
            ->where('created_at', '>=', Carbon::now()->subHours(6))
            ->count();

        $post->score = $score;

        // Enhanced hot score calculation
        // Base score from votes
        $baseScore = max(abs($score), 1);
        $order = log10($baseScore);
        $sign = $score > 0 ? 1 : ($score < 0 ? -1 : 0);

        // Time decay (Reddit's algorithm)
        $seconds = $post->created_at->timestamp - 1134028003;

        // Engagement boost from recent activity
        $engagementBoost = ($recentComments * 0.5) + ($recentViews * 0.1);

        $post->hot_score = round(
            ($sign * $order) + ($seconds / 45000) + $engagementBoost,
            7
        );

        // Update trending status with lower threshold for better visibility
        if (($score >= 3 || $recentComments >= 2) && ! $post->trending_start) {
            $post->trending_start = Carbon::now();
        } elseif ($post->trending_start && $post->trending_start <= Carbon::now()->subHours(24)) {
            // Remove trending after 24 hours
            $post->trending_start = null;
        }

        $post->save();
    }
}
