<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Subfapp;
use App\Policies\CommentPolicy;
use App\Policies\PostPolicy;
use App\Policies\SubfappPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Comment::class => CommentPolicy::class,
        Post::class => PostPolicy::class,
        Subfapp::class => SubfappPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
