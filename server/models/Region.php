<?php
// /server/models/Region.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modernized Region Model
 * Table: regions
 */
class Region extends Model
{
    protected $table = 'regions';

    // Standardized to 'id'
    protected $primaryKey = 'id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'region',
        'country_id'
    ];

    // Table doesn't have timestamps in the new SQL
    public $timestamps = false;

    /**
     * Relationship to the Country
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
}
