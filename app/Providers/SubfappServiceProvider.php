<?php

namespace App\Providers;

use App\Models\Subfapp;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class SubfappServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Inertia::share('subfapps', fn () => Subfapp::select('id', 'name', 'display_name', 'description')
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get()
        );
    }
}
