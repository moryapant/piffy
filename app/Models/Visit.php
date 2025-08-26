<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'ip_address',
        'user_agent',
        'page_visited',
        'page_title',
        'user_id',
        'activity_type',
        'model_id',
        'model_type',
        'activity_data',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'activity_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model based on model_type and model_id
     */
    public function related()
    {
        if ($this->model_type && $this->model_id) {
            $class = '\\App\\Models\\'.$this->model_type;

            return $class::find($this->model_id);
        }

        return null;
    }

    /**
     * Get the post if this activity is related to a post
     */
    public function post()
    {
        if ($this->model_type === 'Post' && $this->model_id) {
            return $this->belongsTo(Post::class, 'model_id');
        }

        return null;
    }

    /**
     * Get the comment if this activity is related to a comment
     */
    public function comment()
    {
        if ($this->model_type === 'Comment' && $this->model_id) {
            return $this->belongsTo(Comment::class, 'model_id');
        }

        return null;
    }
}
