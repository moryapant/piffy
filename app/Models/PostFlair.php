<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostFlair extends Model
{
    protected $fillable = [
        'subfapp_id',
        'name',
        'color',
        'background_color',
        'description',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the subfapp that owns this flair.
     */
    public function subfapp(): BelongsTo
    {
        return $this->belongsTo(Subfapp::class);
    }

    /**
     * Get all posts using this flair.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'flair_id');
    }

    /**
     * Scope to get only active flairs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order flairs by their order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Get the full CSS class for styling this flair.
     */
    public function getStyleClassAttribute(): string
    {
        return sprintf(
            'color: %s; background-color: %s; border: 1px solid %s;',
            $this->color,
            $this->background_color,
            $this->color
        );
    }

    /**
     * Get flair statistics.
     */
    public function getUsageCountAttribute(): int
    {
        return $this->posts()->count();
    }
}
