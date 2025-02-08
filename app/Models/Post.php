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

    public function votes()
    {
        return $this->hasMany(PostVote::class);
    }

    public function images()
    {
        return $this->hasMany(PostImage::class)->orderBy('order');
    }

    public function views()
    {
        return $this->hasMany(PostView::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function userVote()
    {
        return $this->hasOne(PostVote::class)->where('user_id', auth()->id());
    }
}
