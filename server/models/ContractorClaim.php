<?php
// /server/models/ContractorClaim.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractorClaim extends Model
{
    protected $table = 'cde_contractor_claims';

    protected $fillable = [
        'contractor_id',
        'user_id',
        'message',
        'contact_phone',
        'status',
    ];

    protected $casts = [
        'contractor_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
