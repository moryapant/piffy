<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostVoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubfappController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Subfapp routes
Route::resource('subfapps', SubfappController::class);
Route::post('subfapps/{subfapp}/cover', [SubfappController::class, 'updateCover'])->name('subfapps.cover.update');

// Post routes
Route::resource('posts', PostController::class);
Route::post('/posts/{post}/vote', [PostVoteController::class, 'vote'])->name('posts.vote');
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

require __DIR__.'/auth.php';
