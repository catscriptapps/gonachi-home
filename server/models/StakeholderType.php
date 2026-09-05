<?php
// /server/models/StakeholderType.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Real Estate World's own stakeholder categories (Landlord, Tenant,
 * Contractor, etc.) — distinct from the system-wide account permission
 * types in Src\Controller\UserTypesController (Admin/Staff/Registered/...),
 * which control real access control elsewhere in the app and aren't
 * specific to any one project. Currently used as the Mentors module's
 * "who this mentor helps" category.
 */
class StakeholderType extends Model
{
    protected $table = 'rew_stakeholder_types';
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = ['name'];

    protected $casts = [
        'id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function mentors(): HasMany
    {
        return $this->hasMany(Mentor::class, 'target_stakeholder_type_id', 'id');
    }
}
