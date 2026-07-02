<?php
// /resources/views/components/users/data-row.php

/** @var array $avatar */
/** @var string $initials */
/** @var App\Models\User $user */
/** @var string $username */
/** @var bool $isFollowing */
/** @var string $avatar */

?>

<div class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors cursor-pointer group">
    <div class="flex items-center gap-3">
        <?php if ($avatar): ?>
            <img src="<?= $avatar ?>" class="h-10 w-10 rounded-xl object-cover border border-gray-100 dark:border-gray-700">
        <?php else: ?>
            <div class="h-10 w-10 rounded-xl bg-primary-400 flex items-center justify-center text-white font-bold text-sm">
                <?= $initials ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col">
            <span class="text-sm font-bold text-gray-900 dark:text-white">
                <?= htmlspecialchars($user->full_name) ?>
            </span>
            <span class="text-[10px] text-gray-500 font-medium tracking-tight">
                @<?= htmlspecialchars($username) ?>
            </span>
        </div>
    </div>

    <?php if (!$isFollowing): ?>
        <button
            data-action="follow"
            data-user-id="<?= $user->id ?>"
            class="follow-toggle-btn px-3 py-1.5 bg-secondary-400 hover:bg-primary-400 text-white text-[9px] font-black uppercase tracking-widest rounded-lg transition-all active:scale-95">
            Follow
        </button>
    <?php else: ?>
        <button
            data-action="unfollow"
            data-user-id="<?= $user->id ?>"
            class="follow-toggle-btn px-3 py-1.5 bg-gray-200 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all active:scale-95">
            Following
        </button>
    <?php endif; ?>
</div>