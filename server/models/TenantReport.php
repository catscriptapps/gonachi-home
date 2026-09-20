<?php
// /server/models/TenantReport.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantReport extends Model
{
    protected $table = 'ltv_tenant_reports';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'property_address',
        'duration_of_tenancy',
        'conduct_type',
        'notes',
        'rating',
        'reference_name',
        'reference_phone',
        'status',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'user_id' => 'integer',
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(TenantRecord::class, 'tenant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
