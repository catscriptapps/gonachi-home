<?php
// /src/Controller/UserTypesController.php

declare(strict_types=1);

namespace Src\Controller;

/**
 * Fixed role/account-type lookup — there is no user_types table, this is the
 * canonical list referenced by App\Models\User::hasType()'s docblock
 * (Admin = 1, Registered = 2, Staff = 3, Landlord = 4, Tenant = 5, Agent = 6)
 * and stored per-user as the users.user_type_ids JSON array.
 */
class UserTypesController
{
    private const TYPES = [
        1 => 'Admin',
        2 => 'Registered',
        3 => 'Staff',
        4 => 'Landlord',
        5 => 'Tenant',
        6 => 'Agent',
    ];

    /**
     * @return object[] Each with ->user_type_id and ->user_type, matching the
     *                   shape callers already expect (see server/helpers.php's
     *                   getUserRoles() and resources/views/components/users/data-row.php).
     */
    public static function list(): array
    {
        $types = [];
        foreach (self::TYPES as $id => $name) {
            $types[] = (object) ['user_type_id' => $id, 'user_type' => $name];
        }
        return $types;
    }

    public static function label(?int $id): string
    {
        return self::TYPES[$id] ?? 'Expert';
    }
}
