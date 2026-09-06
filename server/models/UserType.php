<?php
// /server/models/UserType.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The system-wide account/stakeholder type list — Admin plus every
 * real-world role a user can identify as (Landlord, Tenant, Contractor,
 * etc.). Matches the legacy gonachi/ platform's users_types table exactly.
 * Stored per-user as the users.user_type_ids JSON array (see
 * App\Models\User::hasType()) rather than a single FK, so there's no real
 * users() relationship here.
 */
class UserType extends Model
{
    protected $table = 'users_types';
    protected $primaryKey = 'user_type_id';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = ['user_type_id', 'user_type'];

    protected $casts = [
        'user_type_id' => 'integer',
    ];

    public const ADMIN = 1;
}
