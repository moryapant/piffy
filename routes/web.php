<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentVoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
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

// Notification routes
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/api/notifications', [NotificationController::class, 'api'])->name('api.notifications');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');
    Route::patch('/api/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.mark-read');
    Route::patch('/api/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
});

// Subfapp routes - Auth required (specific routes first)
Route::middleware('auth')->group(function () {
    Route::get('/subfapps/create', [SubfappController::class, 'create'])->name('subfapps.create');
    Route::post('/subfapps', [SubfappController::class, 'store'])->name('subfapps.store');
    Route::get('/subfapps/{subfapp}/edit', [SubfappController::class, 'edit'])->name('subfapps.edit');
    Route::patch('/subfapps/{subfapp}', [SubfappController::class, 'update'])->name('subfapps.update');
    Route::delete('/subfapps/{subfapp}', [SubfappController::class, 'destroy'])->name('subfapps.destroy');
    Route::post('subfapps/{subfapp}/cover', [SubfappController::class, 'updateCover'])->name('subfapps.cover.update');
    Route::post('subfapps/{subfapp}/avatar', [SubfappController::class, 'updateAvatar'])->name('subfapps.avatar.update');
    Route::post('/subfapps/{subfapp}/join', [SubfappController::class, 'join'])->name('subfapp.join');
    Route::delete('/subfapps/{subfapp}/leave', [SubfappController::class, 'leave'])->name('subfapp.leave');

    // Flair management routes (only for subfapp owners)
    Route::get('/subfapps/{subfapp}/flairs', [\App\Http\Controllers\FlairController::class, 'index'])->name('subfapps.flairs.index');
    Route::post('/subfapps/{subfapp}/flairs', [\App\Http\Controllers\FlairController::class, 'store'])->name('subfapps.flairs.store');
    Route::patch('/subfapps/{subfapp}/flairs/{flair}', [\App\Http\Controllers\FlairController::class, 'update'])->name('subfapps.flairs.update');
    Route::delete('/subfapps/{subfapp}/flairs/{flair}', [\App\Http\Controllers\FlairController::class, 'destroy'])->name('subfapps.flairs.destroy');
    Route::patch('/subfapps/{subfapp}/flairs-order', [\App\Http\Controllers\FlairController::class, 'updateOrder'])->name('subfapps.flairs.update-order');
});

// Subfapp routes - Public (wildcard routes last)
Route::get('/subfapps', [SubfappController::class, 'index'])->name('subfapps.index');
Route::get('/subfapps/{subfapp}', [SubfappController::class, 'show'])->name('subfapps.show');

// Post routes - Auth required (specific routes first)
Route::middleware('auth')->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::patch('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
});

// Post routes - Public (wildcard routes last)
Route::get('/posts', [PostController::class, 'index'])->name('posts.index')->middleware(\App\Http\Middleware\SimpleVisitMiddleware::class);
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show')->middleware(\App\Http\Middleware\SimpleVisitMiddleware::class);
Route::get('/api/trending-posts', [PostController::class, 'trendingPosts'])->name('posts.trending');

// Image view tracking route
Route::post('/posts/{post}/track-image-view', [PostController::class, 'trackImageView'])->name('posts.track-image-view');

// Auth required post actions
Route::middleware('auth')->group(function () {
    Route::post('/posts/{post}/vote', [PostVoteController::class, 'vote'])->name('posts.vote');
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
    Route::post('/posts/{post}/comments/reply', [CommentController::class, 'reply'])->name('posts.comments.reply');
    Route::post('/comments/{comment}/vote', [CommentVoteController::class, 'vote'])->name('comments.vote');
});

// Comment routes
Route::resource('comments', CommentController::class)->except(['create', 'store']);

// Temporary test route
Route::get('/test-storage', function () {
    return response()->file(storage_path('app/public/subfapps/covers/UfN0YfPp2frwzZYZ8Zwrm3rDmnmW70Earz36oGSx.jpg'));
});

// User routes
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show'); // Public user profile
Route::get('/users/{user}/profile', [UserController::class, 'profile'])->name('users.profile'); // Public profile page

Route::middleware('auth')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

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

// Test route for post creation (temporary - no auth required)
Route::get('/test-post-create', [PostController::class, 'create'])->name('test.posts.create');

// Health check route for deployment verification
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => config('app.version', '1.0.0'),
    ]);
})->name('health');

require __DIR__.'/auth.php';
