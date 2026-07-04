<?php
// /server/models/Lead.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'rel_leads';

    protected $fillable = [
        'lead_source_id',
        'external_id',
        'source_url',
        'raw_text',
        'request_type',
        'property_type',
        'bedrooms',
        'location_id',
        'location_raw',
        'budget_min',
        'budget_max',
        'intent_level',
        'contact_info_raw',
        'status',
        'category_id',
        'posted_at',
        'scraped_at',
    ];

    protected $casts = [
        'lead_source_id' => 'integer',
        'bedrooms'       => 'integer',
        'location_id'    => 'integer',
        'budget_min'     => 'decimal:2',
        'budget_max'     => 'decimal:2',
        'category_id'    => 'integer',
        'posted_at'      => 'datetime',
        'scraped_at'     => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function category()
    {
        return $this->belongsTo(LeadCategory::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
