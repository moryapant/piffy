<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function votes()
    {
        return $this->hasMany(PostVote::class);
    }

    public function subfapps()
    {
        return $this->belongsToMany(Subfapp::class, 'user_subfapp');
    }

    // Query scopes for better performance
    public function scopeWithPostStats($query)
    {
        return $query->withCount(['posts', 'comments', 'votes']);
    }

    public function scopeActive($query, int $days = 30)
    {
        return $query->whereHas('posts', function ($postQuery) use ($days) {
            $postQuery->where('created_at', '>=', now()->subDays($days));
        })->orWhereHas('comments', function ($commentQuery) use ($days) {
            $commentQuery->where('created_at', '>=', now()->subDays($days));
        });
    }

    public function scopeTopContributors($query, int $days = 30, int $limit = 10)
    {
        return $query->withPostStats()
            ->active($days)
            ->orderBy('posts_count', 'desc')
            ->limit($limit);
    }

    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }
}
