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
    ];

    protected $with = ['tags'];

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

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function userVote()
    {
        return $this->hasOne(PostVote::class)->where('user_id', auth()->id());
    }
}
