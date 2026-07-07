<?php
// /server/models/LeadUnlock.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadUnlock extends Model
{
    protected $table = 'rel_lead_unlocks';

    protected $fillable = [
        'user_id',
        'lead_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'lead_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
