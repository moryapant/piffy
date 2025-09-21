<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subfapp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'icon',
        'cover_image',
        'created_by',
        'member_count',
        'views_count',
        'type',
        'nsfw',
        'color',
    ];

    protected $casts = [
        'views_count' => 'integer',
        'member_count' => 'integer',
        'nsfw' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($community) {
            // Delete all posts in the community
            $community->posts()->delete();
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_subfapp');
    }

    public function flairs()
    {
        return $this->hasMany(PostFlair::class);
    }

    public function activeFlairs()
    {
        return $this->flairs()->active()->ordered();
    }

    public function rules()
    {
        return $this->hasMany(CommunityRule::class);
    }

    public function activeRules()
    {
        return $this->rules()->active()->ordered();
    }

    public function isPublic(): bool
    {
        return $this->type === 'public';
    }

    public function isRestricted(): bool
    {
        return $this->type === 'restricted';
    }

    public function isPrivate(): bool
    {
        return $this->type === 'private';
    }

    public function isHidden(): bool
    {
        return $this->type === 'hidden';
    }

    public function canView(?User $user): bool
    {
        if ($this->isPublic() || $this->isRestricted()) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->is_admin || $this->created_by === $user->id) {
            return true;
        }

        return $this->users()->where('users.id', $user->id)->exists();
    }

    public function canPost(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isPublic()) {
            return true;
        }

        if ($user->is_admin || $this->created_by === $user->id) {
            return true;
        }

        return $this->users()->where('users.id', $user->id)->exists();
    }

    // Query scopes for better performance
    public function scopeVisibleToUser($query, ?User $user = null)
    {
        if ($user) {
            return $query->where(function ($q) use ($user) {
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
            return $query->whereIn('type', ['public', 'restricted']);
        }
    }

    public function scopePublicAndRestricted($query)
    {
        return $query->whereIn('type', ['public', 'restricted']);
    }

    public function scopeWithCounts($query)
    {
        return $query->withCount(['posts', 'users']);
    }

    public function scopePopular($query, int $limit = 10)
    {
        return $query->withCounts()
            ->orderBy('posts_count', 'desc')
            ->limit($limit);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeWithUserMembership($query, ?User $user = null)
    {
        if ($user) {
            return $query->with(['users' => function ($q) use ($user) {
                $q->where('users.id', $user->id);
            }]);
        }

        return $query;
    }
}
