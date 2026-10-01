<?php
// /server/models/Lead.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
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
        'phone',
        'email',
        'slug',
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

    /**
     * Only leads carrying the 4 pieces of "surface level information buyers
     * seek": a phone number, an email address, a specific (not just
     * country/state-level) resolved location, and a property type. Applied
     * everywhere leads surface publicly (LeadsController, LeadCategoryController)
     * — junk/incomplete scraped rows still get stored (an admin reviewing the
     * pipeline may still want to see them), they just never reach a buyer.
     *
     * "Specific location" = resolves to a depth-2 node in the location tree
     * (an area, e.g. "Lekki") rather than stopping at depth 0 (a bare
     * country) or depth 1 (a bare state) — there's no explicit level column,
     * so this is checked via two parent hops. See Location::parent().
     */
    public function scopeComplete($query)
    {
        self::ensureLeadColumns();

        return $query
            ->whereNotNull('phone')
            ->whereNotNull('email')
            ->whereNotNull('property_type')
            ->whereHas('location', function ($q) {
                $q->whereNotNull('parent_id')
                    ->whereHas('parent', fn ($q2) => $q2->whereNotNull('parent_id'));
            });
    }

    /**
     * Instance-level equivalent of scopeComplete(), for a lead that's
     * already been loaded (with its `location.parent` relation) rather than
     * queried — e.g. leads/detail.php's credit-gated unlock page, which must
     * never let a viewer spend a credit on a lead with no usable phone/email
     * to actually unlock.
     */
    public function isComplete(): bool
    {
        return $this->phone !== null
            && $this->email !== null
            && $this->property_type !== null
            && $this->location !== null
            && $this->location->parent_id !== null
            && $this->location->parent !== null
            && $this->location->parent->parent_id !== null;
    }

    /**
     * Adds the `phone`/`email`/`slug` columns to an already-deployed
     * rel_leads table on first use, without a (data-wiping) full reset —
     * mirrors the lazy-table-creation pattern used elsewhere in this app
     * (e.g. Src\Service\PendingUploadTracker::ensureTable()), generalized to
     * an ALTER since this table already holds real scraped lead data that
     * must never be dropped. A fresh install gets these columns directly
     * from scripts/reset/rel-leads.php instead.
     */
    public static function ensureLeadColumns(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        $schema = Capsule::schema();
        $table = (new self())->getTable();

        if (!$schema->hasColumn($table, 'phone')) {
            $schema->table($table, fn ($t) => $t->string('phone')->nullable()->after('contact_info_raw'));
        }
        if (!$schema->hasColumn($table, 'email')) {
            $schema->table($table, fn ($t) => $t->string('email')->nullable()->after('phone'));
        }
        if (!$schema->hasColumn($table, 'slug')) {
            // Only set for leads with a property_type + specific location
            // (see LeadsController::buildUniqueSlug()) — unique among
            // non-null values only, so incomplete leads can simply leave it
            // null with no collision bookkeeping needed for rows that can
            // never be publicly linked to anyway (see Lead::isComplete()).
            $schema->table($table, fn ($t) => $t->string('slug')->nullable()->unique()->after('email'));
        }
    }
}
