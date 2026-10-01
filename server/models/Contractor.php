<?php
// /server/models/Contractor.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    protected $table = 'cde_contractors';

    protected $fillable = [
        'contractor_source_id',
        'external_id',
        'business_name',
        'slug',
        'service_category',
        'location',
        'operating_areas',
        'phone',
        'email',
        'website',
        'description',
        'rating',
        'review_count',
        'avatar_url',
        'claimed_by_user_id',
        'claim_status',
        'status',
        'outreach_sms_sent_at',
        'outreach_email_sent_at',
    ];

    protected $casts = [
        'contractor_source_id' => 'integer',
        'claimed_by_user_id' => 'integer',
        'rating' => 'decimal:1',
        'review_count' => 'integer',
        'outreach_sms_sent_at' => 'datetime',
        'outreach_email_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function source()
    {
        return $this->belongsTo(ContractorSource::class, 'contractor_source_id');
    }

    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    public function claims()
    {
        return $this->hasMany(ContractorClaim::class, 'contractor_id');
    }

    public function outreachLog()
    {
        return $this->hasMany(ContractorOutreachLog::class, 'contractor_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Adds the `slug`/`avatar_url` columns to an already-deployed
     * cde_contractors table on first use, without a (data-wiping) full
     * reset — mirrors Lead::ensureLeadColumns() exactly. A fresh install
     * gets these columns directly from scripts/reset/cde-contractors.php.
     */
    public static function ensureContractorColumns(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        $schema = Capsule::schema();
        $table = (new self())->getTable();

        if (!$schema->hasColumn($table, 'slug')) {
            $schema->table($table, fn ($t) => $t->string('slug')->nullable()->unique()->after('business_name'));
        }
        if (!$schema->hasColumn($table, 'avatar_url')) {
            $schema->table($table, fn ($t) => $t->string('avatar_url')->nullable()->after('review_count'));
        }
    }
}
