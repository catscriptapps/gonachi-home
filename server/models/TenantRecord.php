<?php
// /server/models/TenantRecord.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantRecord extends Model
{
    protected $table = 'ltv_tenants';

    protected $fillable = [
        'name',
        'normalized_name',
        'reference_phone',
    ];

    public function reports()
    {
        return $this->hasMany(TenantReport::class, 'tenant_id');
    }
}
