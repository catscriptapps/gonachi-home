<?php
// /resources/views/pages/about-our-team.php

declare(strict_types=1);

/** @var string $baseUrl */

/**
 * Clean inline escape helper defined at the top to prevent undefined function errors.
 */
if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Array representation of legacy bio details for structured looping
$teamMembers = [
    [
        'name'     => 'Richard B',
        'title'    => 'Owner - Investor - Property Manager',
        'email'    => 'richard@pmbrokers.ca',
        'website'  => 'http://www.richardbrisson.ca',
        'linkedin' => 'https://ca.linkedin.com/in/richardbrisson',
    ],
    [
        'name'     => 'Jessica S',
        'title'    => 'Accounting – Office Manager',
        'email'    => 'acct@pmbrokers.ca',
        'website'  => null,
        'linkedin' => null,
    ],
    [
        'name'     => 'Mylena V',
        'title'    => 'Rental & Marketing Coordinator',
        'email'    => 'rentals@pmbrokers.ca',
        'website'  => null,
        'linkedin' => null,
    ],
    [
        'name'     => 'Shona F',
        'title'    => 'Law Clerk / Site Administrator',
        'email'    => 'communications@pmbrokers.ca',
        'website'  => null,
        'linkedin' => null,
    ],
    [
        'name'     => 'Denise L',
        'title'    => 'Site Administrator',
        'email'    => 'info@pmbrokers.ca',
        'website'  => null,
        'linkedin' => null,
    ],
];
?>

<div class="bg-gray-50 dark:bg-gray-900/40 py-12 sm:py-20 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-3xl mx-auto mb-16 sm:mb-24">
            <h1 class="text-3xl sm:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight font-sans">
                Meet Our <span class="text-primary-500 dark:text-primary-400">Team</span>
            </h1>
            <p class="mt-4 text-base sm:text-lg text-gray-600 dark:text-gray-400 font-medium">
                The dedicated professionals working behind the scenes to safeguard your residential investments and deliver premier service.
            </p>
            <div class="mt-6 h-1 w-20 bg-primary-500 dark:bg-primary-400 mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch">
            <?php foreach ($teamMembers as $member): ?>
                <div class="bg-primary-100 dark:bg-secondary-900 border border-gray-100 dark:border-gray-800/80 rounded-2xl p-6 sm:p-8 shadow-xl shadow-gray-100/40 dark:shadow-none flex flex-col justify-between transition-all duration-200 hover:-translate-y-1 hover:shadow-2xl hover:border-primary-500/10 dark:hover:border-primary-400/10 group relative overflow-hidden">

                    <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-primary-500/5 to-transparent rounded-bl-full pointer-events-none transition-all duration-200 group-hover:scale-150"></div>

                    <div class="space-y-4">
                        <div class="w-14 h-14 rounded-xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-lg tracking-wider border border-primary-500/10 dark:border-primary-400/5 group-hover:scale-105 transition-transform duration-200">
                            <?= h(substr($member['name'], 0, 2)) ?>
                        </div>

                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white transition-colors group-hover:text-primary-600 dark:group-hover:text-primary-400 font-sans">
                                <?= h($member['name']) ?>
                            </h2>
                            <p class="text-xs sm:text-sm font-semibold text-gray-400 dark:text-gray-500 mt-0.5">
                                <?= h($member['title']) ?>
                            </p>
                        </div>

                        <?php if ($member['website']): ?>
                            <div class="pt-2">
                                <a href="<?= h($member['website']) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-500 dark:text-primary-400 hover:text-primary-600 dark:hover:text-primary-300 transition-colors">
                                    <span>Visit Personal Website</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                    </svg>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-8 pt-4 border-t border-gray-50 dark:border-gray-800/60 flex items-center gap-3">
                        <a href="mailto:<?= h($member['email']) ?>"
                            title="Email <?= h($member['name']) ?>"
                            class="w-9 h-9 rounded-lg border border-gray-100 dark:border-gray-800 flex items-center justify-center text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-150">
                            <span class="sr-only">Email Address</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25H4.5a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0L12 13.5 2.25 6.75" />
                            </svg>
                        </a>

                        <?php if ($member['linkedin']): ?>
                            <a href="<?= h($member['linkedin']) ?>"
                                target="_blank"
                                rel="noopener"
                                title="Connect with <?= h($member['name']) ?> on LinkedIn"
                                class="w-9 h-9 rounded-lg border border-gray-100 dark:border-gray-800 flex items-center justify-center text-gray-400 hover:text-[#0077b5] dark:hover:text-[#0077b5] hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-150">
                                <span class="sr-only">LinkedIn Profile</span>
                                <svg class="w-4 h-4 fill-currentColor" viewBox="0 0 24 24">
                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.779-1.75-1.75s.784-1.75 1.75-1.75 1.75.779 1.75 1.75-.784 1.75-1.75 1.75zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>