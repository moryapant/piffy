<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserActivity extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'activity_type',
        'action',
        'details',
        'subject_type',
        'subject_id',
        'performed_at',
    ];

    protected $casts = [
        'details' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject of the activity.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include activities of a given type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope a query to only include activities within a time period.
     */
    public function scopeWithinPeriod($query, $startDate, $endDate = null)
    {
        $query->where('performed_at', '>=', $startDate);

        if ($endDate) {
            $query->where('performed_at', '<=', $endDate);
        }

        return $query;
    }
}
