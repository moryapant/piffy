<?php

namespace App\Providers;

use App\Models\PostVote;
use App\Observers\PostVoteObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Register observers
        PostVote::observe(PostVoteObserver::class);
    }
}
