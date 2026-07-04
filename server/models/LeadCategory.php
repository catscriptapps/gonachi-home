<?php
// /server/models/LeadCategory.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadCategory extends Model
{
    protected $table = 'rel_lead_categories';

    protected $fillable = [
        'name',
        'slug',
        'request_type',
        'property_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'category_id');
    }
}
