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
}
