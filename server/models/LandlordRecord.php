<?php
// /server/models/LandlordRecord.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;

class LandlordRecord extends Model
{
    protected $table = 'ltv_landlords';

    protected $fillable = [
        'name',
        'normalized_name',
        'phone',
        'user_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function properties()
    {
        return $this->hasMany(PropertyRecord::class, 'landlord_id');
    }

    public function tenancies()
    {
        return $this->hasMany(Tenancy::class, 'landlord_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Adds the `user_id` column to an already-deployed ltv_landlords table
     * on first use, without a (data-wiping) full reset — mirrors
     * PropertyRecord::ensurePropertyColumns().
     */
    public static function ensureLandlordColumns(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        $schema = Capsule::schema();
        $table = (new self())->getTable();

        if (!$schema->hasColumn($table, 'user_id')) {
            $schema->table($table, fn ($t) => $t->unsignedBigInteger('user_id')->nullable()->index()->after('phone'));
        }
    }
}
