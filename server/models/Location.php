<?php
// /server/models/Location.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'rel_locations';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'country_id',
    ];

    protected $casts = [
        'parent_id'  => 'integer',
        'country_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * Adds the `country_id` column to an already-deployed rel_locations
     * table on first use, without a (data-wiping) full reset — mirrors
     * Lead::ensureLeadColumns(). A fresh install gets this column directly
     * from scripts/reset/rel-locations.php.
     */
    public static function ensureLocationColumns(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        $schema = Capsule::schema();
        $table = (new self())->getTable();

        if (!$schema->hasColumn($table, 'country_id')) {
            $schema->table($table, fn ($t) => $t->unsignedInteger('country_id')->nullable()->index()->after('parent_id'));
        }
    }
}
