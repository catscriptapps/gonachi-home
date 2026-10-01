<?php
// /server/models/TenantReport.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;

class TenantReport extends Model
{
    protected $table = 'ltv_tenant_reports';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'property_address',
        'country_id',
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
        'country_id' => 'integer',
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

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Adds the `country_id` column to an already-deployed
     * ltv_tenant_reports table on first use, without a (data-wiping) full
     * reset — mirrors Lead::ensureLeadColumns().
     */
    public static function ensureTenantReportColumns(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        $schema = Capsule::schema();
        $table = (new self())->getTable();

        if (!$schema->hasColumn($table, 'country_id')) {
            $schema->table($table, fn ($t) => $t->unsignedInteger('country_id')->nullable()->index()->after('property_address'));
        }
    }
}
