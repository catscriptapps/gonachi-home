<?php
// /src/Config/NavigationConfig.php

declare(strict_types=1);

namespace Src\Config;

use Src\Service\AuthService;

/**
 * NavigationConfig handles all static data related to the application's
 * primary navigation structure, including link URLs and associated icons.
 */
class NavigationConfig
{
    /**
     * Defines the icon mapping for each navigation link name. The three
     * Gonachi projects reuse ProjectsConfig's icons so they never drift
     * out of sync with the portal tabs / sidebar switcher.
     * @return array<string, string>
     */
    public static function getIcons(): array
    {
        $icons = [
            'Dashboard' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h7v7H3V3zm0 11h7v7H3v-7zm11-11h7v7h-7V3zm0 11h7v7h-7v-7z"/></svg>',
            'Profile' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>',
            'Users' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75a3 3 0 11-6 0 3 3 0 016 0zM6.75 6.75a3 3 0 116 0 a3 3 0 01-6 0zM3 21a6 6 0 0112 0M9 21a6 6 0 0112 0"></path></svg>',
        ];

        foreach (ProjectsConfig::all() as $project) {
            $icons[$project['name']] = '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">' . $project['icon'] . '</svg>';
        }

        return $icons;
    }

    /**
     * Returns custom labels mapping for home/dashboard module cards.
     * @return array<string, string>
     */
    public static function getModuleLabels(): array
    {
        return [
            'Landlords'           => 'Corporate Asset Accounts',
            'Rental Applications' => 'Core Asset Channel',
            'Inspections'         => 'Core Field Channel',
        ];
    }

    /**
     * Returns descriptions mapping for platform service modules.
     * @return array<string, string>
     */
    public static function getModuleDescriptions(): array
    {
        return [
            'Landlords'           => 'Central management index for institutional and single-family asset entities. Audit operational parameters, regional assignments, and billing profiles.',
            'Rental Applications' => 'Prospective tenant application processing engine. Collect verification files, generate risk metrics, and process lease approvals cleanly.',
            'Inspections'         => 'Highly detailed real-time property verification systems. Structured multi-point documentation containing high-resolution asset tracking reports.',
        ];
    }

    /**
     * Returns the navigation links for the current user.
     */
    public static function getNavLinks(bool $isLoggedIn): array
    {
        return $isLoggedIn ? self::authLinks() : self::publicLinks();
    }

    /**
     * Returns all auth-only links.
     */
    public static function authLinks(bool $showAll = false): array
    {
        $base = $_ENV['APP_BASE_PATH'] ?? '';

        $allPossibleApps = [
            'Dashboard' => [
                'url' => $base . '/dashboard',
                'title' => 'Operational Dashboard',
                'summary' => 'Real-time performance index metrics, pending verification alerts, and systemic portfolio analysis logs.'
            ],
        ];

        foreach (ProjectsConfig::all() as $project) {
            $allPossibleApps[$project['name']] = [
                'url' => $base . '/' . $project['slug'],
                'title' => $project['name'],
                'summary' => $project['tagline'],
            ];
        }

        $allPossibleApps['Profile'] = [
            'url' => $base . '/profile',
            'title' => 'Profile Settings',
            'summary' => 'Manage account access keys, administrative security structures, and regional subscription targets.'
        ];

        $allPossibleApps['Users'] = [
            'url' => $base . '/users',
            'title' => 'User Directory Management',
            'summary' => 'Control internal account scopes, assign operational roles, and check localized system access activity logs.'
        ];

        if ($showAll) {
            return $allPossibleApps;
        }

        $visibleLinks = [];
        foreach ($allPossibleApps as $name => $config) {
            if (AuthService::hasAccess($name)) {
                $visibleLinks[$name] = $config;
            }
        }

        return $visibleLinks;
    }

    /**
     * Returns all public links.
     */
    private static function publicLinks(): array
    {
        $base = $_ENV['APP_BASE_PATH'] ?? '';
        $descriptions = self::getModuleDescriptions();

        return [
            'Home' => [
                'url' => $base . '/home',
                'title' => 'Home',
                'summary' => ''
            ],
            'About' => [
                'url' => $base . '/about',
                'title' => 'About Infrastructure',
                'summary' => 'Learn more about our core philosophy, engineering guidelines, and localized property operational pipelines.',
                'children' => [
                    'About Us'     => ['url' => $base . '/about', 'title' => 'About Us', 'summary' => 'Our core background, architectural focus, and technical values.'],
                    'Our Team'     => ['url' => $base . '/team', 'title' => 'Our Team', 'summary' => 'Meet the platform developers and asset managers working behind the pipeline scenes.'],
                    'Testimonials' => ['url' => $base . '/testimonials', 'title' => 'Client Testimonials', 'summary' => 'Verified case reviews from institutional property managers and independent portfolio landlords.'],
                    'FAQs'         => ['url' => $base . '/faqs', 'title' => 'Frequently Asked Questions', 'summary' => 'Detailed information regarding security clearances, runtime configurations, and module billing metrics.'],
                ]
            ],
            'Services' => [
                'url' => $base . '/services',
                'title' => 'Service Suite Modules',
                'summary' => 'Deploy critical cloud micro-infrastructure optimized to secure rental tenants and shield field inventory objects.',
                'children' => [
                    'Rental Applications' => ['url' => $base . '/rental-applications', 'title' => 'Rental Applications Suite', 'summary' => $descriptions['Rental Applications']],
                    'Inspections'         => ['url' => $base . '/inspections', 'title' => 'Field Inspection Suite', 'summary' => $descriptions['Inspections']],
                ]
            ],
            'Contact' => [
                'url' => $base . '/contact',
                'title' => 'Contact Engineering',
                'summary' => 'Open an operational pipeline help desk ticket or establish secure contact channels directly with our system operators.'
            ],
        ];
    }

    /**
     * Returns the protected paths for route guarding.
     */
    public static function getProtectedPaths(): array
    {
        $base = $_ENV['APP_BASE_PATH'] ?? '';

        return [
            $base . '/dashboard',
            $base . '/landlords',
            $base . '/profile',
            $base . '/users',
            $base . '/properties',
            $base . '/lead-review',
        ];
    }

    /**
     * Paths that require AuthService::isAdmin(), beyond the generic
     * login/app-access model in authLinks(). Checked in index.php before
     * any layout output starts (a page-level header() redirect can't work —
     * the layout already echoes the sidebar/header before including the
     * page file).
     * @return string[]
     */
    public static function getAdminOnlyPaths(): array
    {
        $base = $_ENV['APP_BASE_PATH'] ?? '';

        return [
            $base . '/lead-review',
        ];
    }

    /**
     * Returns the explicit page-scoped routing endpoints for core modules.
     * @return array<string, string>
     */
    public static function getModuleLinks(): array
    {
        $base = $_ENV['APP_BASE_PATH'] ?? '';

        return [
            'Landlords'           => $base . '/landlords',
            'Rental Applications' => $base . '/rental-applications',
            'Inspections'         => $base . '/inspections',
        ];
    }

    /**
     * Gets display information for the currently authenticated entity (User or Landlord).
     * @return array{displayName: string, initial: string}
     */
    public static function getUserDisplayInfo(): array
    {
        $displayName = 'Account';
        $initial = 'G'; // Guest initial

        if (AuthService::isLoggedIn()) {
            $landlord = AuthService::currentLandlord();
            $user = AuthService::currentUser();

            if ($landlord) {
                // Landlord is logged in
                $displayName = $landlord->company_name;
                $initial = !empty($landlord->company_name) ? strtoupper(substr($landlord->company_name, 0, 1)) : 'L';
            } elseif ($user) {
                // Backend user is logged in
                $parts = explode(' ', $user->full_name);
                $displayName = (count($parts) > 1)
                    ? strtoupper(substr($parts[0], 0, 1)) . '. ' . end($parts)
                    : ($parts[0] ?? 'User');
                $initial = !empty($user->full_name) ? strtoupper(substr($user->full_name, 0, 1)) : 'U';
            }
        }
        return compact('displayName', 'initial');
    }

    /**
     * Returns the navigation links for the landlord dashboard workspace.
     * @return array<string, array<string, string>>
     */
    public static function getLandlordWorkspaceLinks(): array
    {
        $baseUrl = $_ENV['APP_BASE_PATH'] ?? '';

        return [
            'Properties' => [
                'url' => $baseUrl . '/properties', // This page will need to be created
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>',
                'title' => 'Property Portfolio',
                'summary' => 'View and manage your assigned property portfolio.'
            ],
            'Services' => [
                'url' => $baseUrl . '/services',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18M3 7.5l1.5 12h15L21 7.5M8.25 7.5V6a3.75 3.75 0 117.5 0v1.5" /></svg>',
                'title' => 'Subscribed Services',
                'summary' => 'Manage your active service subscriptions.'
            ],
            'Access Tokens' => [
                'url' => $baseUrl . '/access-tokens',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 11-12 0 6 6 0 0112 0zM2.25 21a8.966 8.966 0 015.06-8.006" /></svg>',
                'title' => 'Access Tokens',
                'summary' => 'View intake keys generated for your properties.'
            ],
            'Billing' => [
                'url' => '#', // Future link
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>',
                'title' => 'Billing & Invoices',
                'summary' => 'Access your statements and billing history.'
            ],
            'Support' => [
                'url' => $baseUrl . '/contact',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:rotate-45 transition-transform duration-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a7.75 7.75 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                    </svg>',
                'title' => 'Support Center',
                'summary' => 'Open a help desk ticket or contact our team.'
            ]
        ];
    }
}
