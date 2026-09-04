<?php
// /src/Config/ProjectsConfig.php

declare(strict_types=1);

namespace Src\Config;

/**
 * Single source of truth for the three data-engine projects under
 * gonachi-home — used by the portal landing page and by each project's
 * sidebar "switch project" card, so identity/icons/colors stay in sync.
 * Real Estate World is a fourth project but isn't listed here (see
 * resources/views/pages/home.php and partials/project-switcher.php,
 * which each add it in separately) — it's a different shape (a global
 * submission platform, not a leads-extraction engine).
 */
class ProjectsConfig
{
    /**
     * @return array<int, array{slug: string, name: string, tagline: string, status: string, accent: string, icon: string}>
     */
    public static function all(): array
    {
        return [
            [
                'slug' => 'real-estate-leads',
                'name' => 'Real Estate Leads',
                'tagline' => 'Find people who are actively looking to buy or sell property.',
                'status' => 'live',
                'accent' => 'primary',
                // Funnel icon — distinct from the house icon used by the
                // sidebar's "Gonachi Home" hub link and breadcrumbs.php.
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4.5h18M3 4.5v1.086c0 .464.184.909.513 1.237l5.974 5.974c.329.328.513.773.513 1.237v5.148c0 .687.56 1.212 1.229 1.318a48.62 48.62 0 003.542 0c.669-.106 1.229-.63 1.229-1.318v-5.148c0-.464.184-.909.513-1.237l5.974-5.974A1.75 1.75 0 0021 5.586V4.5" />',
            ],
            [
                'slug' => 'contractor-discovery',
                'name' => 'Contractor Discovery',
                'tagline' => 'The largest searchable contractor database and job marketplace in Nigeria.',
                'status' => 'live',
                'accent' => 'secondary',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />',
            ],
            [
                'slug' => 'landlord-tenant-validation',
                'name' => 'Landlord & Tenant Validation',
                'tagline' => 'A searchable database of landlord and tenant records — a credit bureau, but for renting.',
                'status' => 'live',
                'accent' => 'indigo',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />',
            ],
        ];
    }
}
