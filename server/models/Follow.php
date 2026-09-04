<?php
// /server/models/Follow.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $table = 'rew_follows';

    protected $fillable = [
        'follower_id',
        'following_id',
    ];

    protected $casts = [
        'follower_id' => 'integer',
        'following_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function following()
    {
        return $this->belongsTo(User::class, 'following_id');
    }
}
