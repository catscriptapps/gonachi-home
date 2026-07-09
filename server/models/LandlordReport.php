<?php
// /server/models/LandlordReport.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordReport extends Model
{
    protected $table = 'ltv_reports';

    protected $fillable = [
        'property_id',
        'landlord_id',
        'user_id',
        'duration_of_tenancy',
        'issue_type',
        'notes',
        'status',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'landlord_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(PropertyRecord::class, 'property_id');
    }

    public function landlord()
    {
        return $this->belongsTo(LandlordRecord::class, 'landlord_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function photos()
    {
        return $this->hasMany(LandlordReportPhoto::class, 'report_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
