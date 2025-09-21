<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityRule extends Model
{
    protected $fillable = [
        'subfapp_id',
        'title',
        'description',
        'type',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the subfapp that owns this rule.
     */
    public function subfapp(): BelongsTo
    {
        return $this->belongsTo(Subfapp::class);
    }

    /**
     * Scope to get only active rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order rules by their order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('title');
    }

    /**
     * Scope to filter by rule type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the icon for the rule type.
     */
    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'rule' => '⚠️',
            'guideline' => '💡',
            'policy' => '📋',
            default => '📝',
        };
    }

    /**
     * Get the formatted type name.
     */
    public function getFormattedTypeAttribute(): string
    {
        return ucfirst($this->type);
    }
}
