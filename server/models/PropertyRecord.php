<?php
// /server/models/PropertyRecord.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;

class PropertyRecord extends Model
{
    protected $table = 'ltv_properties';

    protected $fillable = [
        'landlord_id',
        'address',
        'normalized_address',
        'property_type',
        'country_id',
    ];

    protected $casts = [
        'landlord_id' => 'integer',
        'country_id' => 'integer',
    ];

    public function landlord()
    {
        return $this->belongsTo(LandlordRecord::class, 'landlord_id');
    }

    public function reports()
    {
        return $this->hasMany(LandlordReport::class, 'property_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * Adds the `country_id` column to an already-deployed ltv_properties
     * table on first use, without a (data-wiping) full reset — mirrors
     * Lead::ensureLeadColumns(). A fresh install gets this column directly
     * from scripts/reset/ltv-properties.php.
     */
    public static function ensurePropertyColumns(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        $schema = Capsule::schema();
        $table = (new self())->getTable();

        if (!$schema->hasColumn($table, 'country_id')) {
            $schema->table($table, fn ($t) => $t->unsignedInteger('country_id')->nullable()->index()->after('property_type'));
        }
    }
}
