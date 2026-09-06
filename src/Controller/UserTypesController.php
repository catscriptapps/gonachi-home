<?php
// /src/Controller/UserTypesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\UserType;

/**
 * The system-wide account/stakeholder type list — backed by the
 * users_types table (matching the legacy gonachi/ platform exactly:
 * Admin, Landlord, Tenant, Property Manager, Real Estate Agent,
 * Contractor, Mortgage Broker, User). Stored per-user as the
 * users.user_type_ids JSON array (see App\Models\User::hasType()).
 */
class UserTypesController
{
    /**
     * @return object[] Each with ->user_type_id and ->user_type, matching the
     *                   shape callers already expect (see server/helpers.php's
     *                   getUserRoles() and resources/views/components/users/data-row.php).
     */
    public static function list(): array
    {
        return UserType::orderBy('user_type_id')->get(['user_type_id', 'user_type'])->all();
    }

    public static function label(?int $id): string
    {
        if (!$id) {
            return 'User';
        }

        return UserType::find($id)->user_type ?? 'User';
    }
}
