<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\CacheService;

class PostObserver
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        $this->clearRelevantCaches();
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        $this->clearRelevantCaches();
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        $this->clearRelevantCaches();
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void
    {
        $this->clearRelevantCaches();
    }

    /**
     * Handle the Post "force deleted" event.
     */
    public function forceDeleted(Post $post): void
    {
        $this->clearRelevantCaches();
    }

    protected function clearRelevantCaches(): void
    {
        $this->cacheService->clearTrendingPostsCache();
        $this->cacheService->clearSiteStatsCache();
    }
}
