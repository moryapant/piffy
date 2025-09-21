<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content',
        'user_id',
        'post_id',
        'parent_id',
        'upvotes',
        'downvotes',
    ];

    protected $appends = ['user_vote', 'score'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function votes()
    {
        return $this->hasMany(CommentVote::class);
    }

    public function userVote(?int $userId = null)
    {
        return $this->hasOne(CommentVote::class)
            ->where('user_id', $userId ?? auth()->id());
    }

    // Accessor to provide user_vote for JSON serialization
    protected function getUserVoteAttribute()
    {
        // If votes are loaded and filtered by user, return the first one
        if ($this->relationLoaded('votes') && $this->votes->count() > 0) {
            return $this->votes->first();
        }

        return null;
    }

    // Calculate vote score
    public function getScoreAttribute()
    {
        return $this->upvotes - $this->downvotes;
    }
}
