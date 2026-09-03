<?php
// /server/models/SavedSearch.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedSearch extends Model
{
    protected $table = 'rel_saved_searches';

    protected $fillable = [
        'user_id',
        'search_query',
        'region_slug',
        'last_viewed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'last_viewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
