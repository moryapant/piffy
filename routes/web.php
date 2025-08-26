<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostVoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubfappController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('home')->middleware(\App\Http\Middleware\SimpleVisitMiddleware::class);

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
});

// Subfapp routes
Route::middleware('auth')->group(function () {
    Route::resource('subfapps', SubfappController::class);
    Route::post('subfapps/{subfapp}/cover', [SubfappController::class, 'updateCover'])->name('subfapps.cover.update');
    Route::post('subfapps/{subfapp}/avatar', [SubfappController::class, 'updateAvatar'])->name('subfapps.avatar.update');
    Route::post('/subfapps/{subfapp}/join', [SubfappController::class, 'join'])->name('subfapp.join');
    Route::delete('/subfapps/{subfapp}/leave', [SubfappController::class, 'leave'])->name('subfapp.leave');
});

// Post routes
Route::resource('posts', PostController::class)->middleware(\App\Http\Middleware\SimpleVisitMiddleware::class);
Route::post('/posts/{post}/vote', [PostVoteController::class, 'vote'])->name('posts.vote');
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
Route::post('/posts/{post}/comments/reply', [CommentController::class, 'reply'])->name('posts.comments.reply');

// Comment routes
Route::resource('comments', CommentController::class)->except(['create', 'store']);

// Temporary test route
Route::get('/test-storage', function () {
    return response()->file(storage_path('app/public/subfapps/covers/UfN0YfPp2frwzZYZ8Zwrm3rDmnmW70Earz36oGSx.jpg'));
});

// User routes
Route::resource('users', UserController::class);
Route::get('/users/{user}/profile', [UserController::class, 'profile'])->name('users.profile');

// Test route with simple middleware
Route::get('/test-simple', function () {
    return 'Testing the simple middleware. Check the DB for a new record.';
})->middleware(\App\Http\Middleware\SimpleVisitMiddleware::class);

// Test route with class-based middleware
Route::get('/test-direct', function () {
    return 'Testing direct middleware reference. Check the DB for a new record.';
})->middleware(\App\Http\Middleware\SimpleVisitMiddleware::class);

// Visit routes
Route::middleware(['auth'])->group(function () {
    Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
    Route::get('/visits/{visit}', [VisitController::class, 'show'])->name('visits.show');
    Route::get('/visits/export', [VisitController::class, 'export'])->name('visits.export');
});

// API route for recent activity (no auth required for public activity)
Route::get('/api/recent-activity', [VisitController::class, 'recentActivity'])->name('api.recent-activity');

// API route for trending posts (public endpoint)
Route::get('/api/trending-posts', [PostController::class, 'trendingPosts'])->name('api.trending-posts')->middleware('web');

// Admin routes
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::delete('/bulk-delete', [AdminController::class, 'bulkDelete'])->name('admin.bulk-delete');
    Route::get('/export', [AdminController::class, 'exportData'])->name('admin.export');
    Route::get('/analytics', [AdminController::class, 'getAnalytics'])->name('admin.analytics');
    Route::delete('/posts/{post}', [AdminController::class, 'deletePost'])->name('admin.posts.delete');
    Route::patch('/comments/{comment}', [AdminController::class, 'updateComment'])->name('admin.comments.update');
    Route::delete('/comments/{comment}', [AdminController::class, 'deleteComment'])->name('admin.comments.delete');
    Route::post('/comments/{comment}/restore', [AdminController::class, 'restoreComment'])->name('admin.comments.restore');
    Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::patch('/communities/{community}', [AdminController::class, 'updateCommunity'])->name('admin.communities.update');
    Route::delete('/communities/{community}', [AdminController::class, 'deleteCommunity'])->name('admin.communities.delete');
});

// Health check route for deployment verification
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => config('app.version', '1.0.0')
    ]);
})->name('health');

require __DIR__.'/auth.php';
