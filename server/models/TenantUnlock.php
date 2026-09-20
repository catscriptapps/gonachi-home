<?php
// /server/models/TenantUnlock.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUnlock extends Model
{
    protected $table = 'ltv_tenant_unlocks';

    protected $fillable = [
        'user_id',
        'tenant_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'tenant_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->belongsTo(TenantRecord::class, 'tenant_id');
    }
}
