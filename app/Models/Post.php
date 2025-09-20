<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'subfapp_id',
        'user_id',
        'upvotes',
        'downvotes',
        'comment_count',
        'score',
        'hot_score',
        'views_count',
        'trending_start',
    ];

    protected $with = ['tags'];

    protected $casts = [
        'hot_score' => 'float',
        'trending_start' => 'datetime',
        'score' => 'integer',
        'views_count' => 'integer',
        'upvotes' => 'integer',
        'downvotes' => 'integer',
        'comment_count' => 'integer',
    ];

    public function subfapp()
    {
        return $this->belongsTo(Subfapp::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function images()
    {
        return $this->hasMany(PostImage::class)->orderBy('order');
    }

    public function votes()
    {
        return $this->hasMany(PostVote::class);
    }

    public function views()
    {
        return $this->hasMany(PostView::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function userVote(?int $userId = null)
    {
        return $this->hasOne(PostVote::class)
            ->where('user_id', $userId ?? auth()->id());
    }

    // Query scopes for better performance
    public function scopeWithUserData($query, ?User $user = null)
    {
        return $query->with(['user', 'subfapp', 'images', 'tags'])
            ->withCount('comments')
            ->when($user, function ($q) use ($user) {
                $q->with(['votes' => function ($voteQuery) use ($user) {
                    $voteQuery->where('user_id', $user->id);
                }]);
            });
    }

    public function scopeVisibleToUser($query, ?User $user = null)
    {
        return $query->whereHas('subfapp', function ($subfappQuery) use ($user) {
            if ($user) {
                $subfappQuery->where(function ($q) use ($user) {
                    $q->whereIn('type', ['public', 'restricted'])
                      ->orWhereHas('users', function ($userQuery) use ($user) {
                          $userQuery->where('users.id', $user->id);
                      })
                      ->orWhere('created_by', $user->id);
                    
                    if ($user->is_admin) {
                        $q->orWhereIn('type', ['private', 'hidden']);
                    }
                });
            } else {
                $subfappQuery->where('type', 'public');
            }
        });
    }

    public function scopeForHomeFeed($query, ?User $user = null, string $sort = 'hot')
    {
        $query = $query->withUserData($user)
            ->visibleToUser($user);

        return match ($sort) {
            'new' => $query->latest('created_at'),
            'top' => $query->where('created_at', '>=', now()->subHours(6))
                          ->orderBy('score', 'desc'),
            'rising' => $query->where('created_at', '>=', now()->subHours(2))
                             ->where('score', '>', 0)
                             ->orderBy('score', 'desc'),
            default => $query->orderBy('hot_score', 'desc'),
        };
    }
}
