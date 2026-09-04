<?php
// /server/models/Post.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'rew_posts';

    protected $fillable = [
        'user_id',
        'content',
        'media_url',
        'media_type',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class, 'post_id')->orderBy('created_at', 'asc');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    /**
     * Posts by the given author IDs — the feed is "me + who I follow", not
     * a global stream (see SocialFeedController::feed()).
     */
    public function scopeFromAuthors($query, array $userIds)
    {
        return $query->whereIn('user_id', $userIds);
    }
}
