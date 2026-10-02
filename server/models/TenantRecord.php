<?php
// /server/models/TenantRecord.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;

class TenantRecord extends Model
{
    protected $table = 'ltv_tenants';

    protected $fillable = [
        'name',
        'normalized_name',
        'reference_phone',
        'user_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function tenancies()
    {
        return $this->hasMany(Tenancy::class, 'tenant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Adds the `user_id` column to an already-deployed ltv_tenants table on
     * first use, without a (data-wiping) full reset — mirrors
     * PropertyRecord::ensurePropertyColumns().
     */
    public static function ensureTenantColumns(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        $schema = Capsule::schema();
        $table = (new self())->getTable();

        if (!$schema->hasColumn($table, 'user_id')) {
            $schema->table($table, fn ($t) => $t->unsignedBigInteger('user_id')->nullable()->index()->after('reference_phone'));
        }
    }
}
