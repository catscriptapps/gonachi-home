<?php
// /resources/views/pages/profile.php

use App\Utils\IdEncoder;

/** @var \App\Models\User $currentUser */
/** @var \App\Models\User $user */
/** @var string $assetBase */

$user = $currentUser;

// New Model Mapping
$fullName = $user->full_name; // Uses the getFullNameAttribute() accessor
$initials = strtoupper(substr($user->first_name ?? 'U', 0, 1));
$statusIsActive = ((int)$user->status_id === 1);

// Location Logic
$regionName = $user->region->name ?? $user->region->region ?? '';
$countryName = $user->country->name ?? $user->country->country ?? '';

// Avatar Logic (Preserved)
$hasAvatar = !empty($user->avatar_url);
$AVATAR_DIR_PREFIX = $assetBase . 'images/uploads/avatars/';
$avatarUrl = $hasAvatar ? htmlspecialchars($AVATAR_DIR_PREFIX . $user->avatar_url) : '';

// Role Mapping - DYNAMIC DB FETCH (Replacing static array)
if (!isset($GLOBALS['allUserTypes'])) {
    $types = \Src\Controller\UserTypesController::list();
    $GLOBALS['allUserTypes'] = [];
    foreach ($types as $t) {
        $GLOBALS['allUserTypes'][$t->user_type_id] = $t->user_type;
    }
}

$primaryRole = 'User Profile';
?>

<div id="partial-profile" class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] px-4 sm:px-6 lg:px-8 pb-10">
    <div class="max-w-7xl mx-auto">

        <div class="relative overflow-hidden rounded-[2.5rem] bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/5 shadow-xl mb-6 group/hero" data-aos="zoom-in">
            <div class="absolute -top-24 -left-24 w-72 h-72 bg-primary-500/10 rounded-full blur-[80px]"></div>

            <div class="relative p-6 md:p-8 flex flex-col md:flex-row items-center gap-8">

                <div class="relative" id="avatar-preview-wrapper">
                    <div id="avatar-container"
                        data-action="view-avatar"
                        data-img-src="<?= $avatarUrl; ?>"
                        class="h-32 w-32 md:h-36 md:w-36 rounded-[2rem] overflow-hidden ring-4 ring-gray-50 dark:ring-white/5 shadow-2xl bg-gradient-to-br from-primary-500 to-secondary-600 flex items-center justify-center transition-all duration-500 group-hover:scale-105 <?= $hasAvatar ? 'cursor-zoom-in' : ''; ?>">

                        <span id="avatar-initial" class="text-5xl font-black text-white tracking-tighter <?= $hasAvatar ? 'hidden' : 'block'; ?>">
                            <?= $initials; ?>
                        </span>

                        <img id="avatar-img" src="<?= $avatarUrl; ?>" alt="Profile"
                            class="w-full h-full object-cover <?= $hasAvatar ? 'block' : 'hidden'; ?>">

                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" stroke-width="3" />
                            </svg>
                        </div>
                    </div>

                    <button id="change-avatar-btn" data-action="upload"
                        class="absolute -bottom-1 -right-1 p-2.5 bg-primary-500 text-white rounded-xl shadow-xl hover:bg-primary-400 transition-all z-10 border-2 border-white dark:border-gray-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" stroke-width="2.5" />
                        </svg>
                    </button>
                    <input type="file" id="avatar-file-input" class="hidden" accept="image/*">
                </div>

                <div class="flex-1 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-2 py-0.5 rounded-full bg-primary-500/10 text-primary-500 text-[9px] font-black uppercase tracking-widest mb-2">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-primary-500"></span>
                        </span>
                        <?= $primaryRole ?>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-black text-secondary-900 dark:text-white tracking-tighter leading-tight mb-1" data-field="fullName">
                        <?= htmlspecialchars($fullName); ?>
                    </h1>

                    <p class="text-sm text-gray-400 font-medium mb-4">
                        Member since <?= $user->date_created->format('M Y') ?>
                    </p>

                    <div class="flex flex-wrap justify-center md:justify-start gap-2">
                        <button
                            data-action="edit-user-profile"
                            data-encoded-id="<?= IdEncoder::encode($user->id); ?>"
                            data-first-name="<?= htmlspecialchars($user->first_name); ?>"
                            data-last-name="<?= htmlspecialchars($user->last_name); ?>"
                            data-full-name="<?= htmlspecialchars($user->full_name); ?>"
                            data-email="<?= htmlspecialchars($user->email); ?>"
                            data-city="<?= htmlspecialchars($user->city ?? ''); ?>"
                            data-country-id="<?= $user->country_id ?? 0; ?>"
                            data-region-id="<?= $user->region_id ?? 0; ?>"
                            data-is-active="<?= $statusIsActive ? '1' : '0'; ?>"
                            data-avatar-url="<?= htmlspecialchars($user->avatar_url ?? ''); ?>"
                            data-user-type-ids='<?= json_encode($user->user_type_ids ?? []); ?>'
                            class="px-5 py-2.5 bg-secondary-900 dark:bg-gray-400 text-white dark:text-secondary-900 rounded-xl font-black text-xs transition-all hover:-translate-y-0.5 shadow-lg flex items-center gap-2 group">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Modify Profile</span>
                        </button>
                    </div>
                </div>

                <div class="hidden xl:flex flex-col items-end gap-2 border-l border-gray-100 dark:border-white/5 pl-8">
                    <p class="text-[9px] font-black uppercase text-gray-400 tracking-[0.2em] mb-1">Assigned Roles</p>
                    <div class="flex flex-col gap-1.5">
                        <?php foreach ($user->user_type_ids ?? [] as $tid): ?>
                            <?php $roleName = htmlspecialchars((string)($GLOBALS['allUserTypes'][$tid] ?? 'User')); ?>
                            <span class="w-28 py-1 rounded-md text-[9px] font-black uppercase bg-gray-100 dark:bg-slate-800 text-slate-500 border border-gray-200 dark:border-slate-700 whitespace-nowrap text-center inline-block">
                                <?= $roleName ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-[2rem] p-6 border border-gray-100 dark:border-white/5 shadow-sm">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Identity Signature</label>
                        <div class="flex items-center gap-2">
                            <p class="text-base text-secondary-900 dark:text-white font-bold truncate" data-field="email"><?= htmlspecialchars($user->email); ?></p>
                            <?php if ($user->email_verified): ?>
                                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                </svg>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Geo-Location</label>
                        <p class="text-base text-secondary-900 dark:text-white font-bold">
                            <?= htmlspecialchars($user->city ?: 'Unset') ?><?= $regionName ? ", $regionName" : "" ?>
                            <span class="text-gray-400 font-medium text-sm"><?= $countryName ? "($countryName)" : "" ?></span>
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-50 dark:border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Last Authentication</p>
                        <p class="text-sm font-bold text-secondary-900 dark:text-white"><?= $user->user_last_log ? $user->user_last_log->diffForHumans() : 'New Connection' ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">System Security</p>
                        <p class="text-sm font-bold text-primary-500">AES-256 Encrypted</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-secondary-950 rounded-[2rem] p-6 text-white relative overflow-hidden shadow-xl h-full flex flex-col">
                    <div class="absolute -right-4 -bottom-4 opacity-10 rotate-12 pointer-events-none">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-7.618 3.04c0 4.833 3.07 9.363 7.618 11.016a11.955 11.955 0 017.618-11.016z" />
                        </svg>
                    </div>

                    <div class="relative z-10 mb-6">
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-primary-400 mb-1">System Integrity</p>
                        <h3 class="text-xl font-black mb-4">Account Status</h3>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center border-b border-white/5 pb-2">
                                <span class="text-[11px] font-bold text-gray-400">Status</span>
                                <span class="px-2 py-0.5 bg-green-500/20 text-green-400 rounded-md text-[9px] font-black uppercase tracking-tighter">Verified</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-gray-400">Encryption</span>
                                <span class="text-[11px] font-black text-primary-400 tracking-widest uppercase">AES-256</span>
                            </div>
                        </div>
                    </div>

                    <div id="delete-avatar-container" class="relative z-10 mt-auto" style="display: <?= $hasAvatar ? 'block' : 'none'; ?>;">
                        <button id="delete-avatar-btn"
                            data-action="delete-avatar"
                            data-id="<?= IdEncoder::encode($user->id); ?>"
                            class="w-full py-3 bg-red-500/10 hover:bg-red-600 text-red-400 hover:text-white border border-red-500/20 rounded-xl font-black text-[9px] uppercase tracking-widest transition-all active:scale-95">
                            Purge Media
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>