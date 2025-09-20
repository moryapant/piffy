<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Subfapp;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    const CACHE_TTL_SHORT = 900; // 15 minutes
    const CACHE_TTL_MEDIUM = 1800; // 30 minutes
    const CACHE_TTL_LONG = 3600; // 1 hour

    /**
     * Get popular communities with caching
     */
    public function getPopularCommunities(?User $user = null, int $limit = 5): array
    {
        $cacheKey = 'popular_communities_' . ($user ? 'user_' . $user->id : 'guest') . '_' . $limit;
        
        return Cache::remember($cacheKey, self::CACHE_TTL_MEDIUM, function () use ($user, $limit) {
            $query = Subfapp::withCount(['posts', 'users']);

            if ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('type', '!=', 'hidden')
                        ->orWhere('created_by', $user->id)
                        ->orWhereHas('users', function ($userQuery) use ($user) {
                            $userQuery->where('users.id', $user->id);
                        });

                    if ($user->is_admin) {
                        $q->orWhere('type', 'hidden');
                    }
                });
            } else {
                $query->whereIn('type', ['public', 'restricted']);
            }

            return $query->orderBy('posts_count', 'desc')
                ->take($limit)
                ->get()
                ->map(function ($community) {
                    return [
                        'id' => $community->id,
                        'name' => $community->name,
                        'display_name' => $community->display_name,
                        'icon' => $community->cover_image,
                        'avtaar' => $community->icon,
                        'posts_count' => $community->posts_count,
                        'member_count' => $community->users_count,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get trending posts with caching
     */
    public function getTrendingPosts(string $sort = 'hot', ?User $user = null, int $limit = 10): array
    {
        $cacheKey = 'trending_posts_' . $sort . '_' . ($user ? 'user_' . $user->id : 'guest') . '_' . $limit;
        
        return Cache::remember($cacheKey, self::CACHE_TTL_SHORT, function () use ($sort, $user, $limit) {
            $query = Post::forHomeFeed($user, $sort);
            
            return $query->take($limit)
                ->get(['id', 'title', 'hot_score', 'score', 'created_at'])
                ->toArray();
        });
    }

    /**
     * Get site statistics with caching
     */
    public function getSiteStats(): array
    {
        return Cache::remember('site_stats', self::CACHE_TTL_LONG, function () {
            return [
                'total_posts' => Post::count(),
                'total_communities' => Subfapp::count(),
                'total_users' => User::count(),
                'posts_today' => Post::whereDate('created_at', today())->count(),
            ];
        });
    }

    /**
     * Clear cache for specific patterns
     */
    public function clearCache(string $pattern): void
    {
        $keys = Cache::getRedis()->keys($pattern);
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }

    /**
     * Clear popular communities cache
     */
    public function clearPopularCommunitiesCache(): void
    {
        $this->clearCache('*popular_communities*');
    }

    /**
     * Clear trending posts cache
     */
    public function clearTrendingPostsCache(): void
    {
        $this->clearCache('*trending_posts*');
    }

    /**
     * Clear site stats cache
     */
    public function clearSiteStatsCache(): void
    {
        Cache::forget('site_stats');
    }

    /**
     * Clear all application caches
     */
    public function clearAllCache(): void
    {
        $this->clearPopularCommunitiesCache();
        $this->clearTrendingPostsCache();
        $this->clearSiteStatsCache();
    }
}
