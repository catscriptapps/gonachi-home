<?php
// /resources/views/components/access-tokens/token-row.php

/** @var array $item */

$statusStyles = [
    'active'  => 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10',
    'revoked' => 'text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50',
];

$status = $item['status'] ?? 'active';
$statusClass = $statusStyles[$status] ?? $statusStyles['active'];
$isActive = $status === 'active';
?>

<tr id="access-token-row-<?= $item['id'] ?? '0' ?>" data-id="<?= $item['id'] ?? '0' ?>" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-all border-b border-gray-100 dark:border-gray-800 font-sans">

    <td class="px-6 py-4 align-top">
        <span class="text-sm font-black text-gray-900 dark:text-white font-mono"><?= htmlspecialchars($item['token_code'] ?? '') ?></span>
        <div class="md:hidden mt-1 text-xs text-gray-500 dark:text-gray-400">
            <?= htmlspecialchars($item['property_label'] ?? '') ?> &middot; <?= htmlspecialchars($item['service_name'] ?? '') ?>
        </div>
    </td>

    <td class="px-6 py-4 hidden md:table-cell align-top">
        <span class="text-xs font-bold text-gray-700 dark:text-gray-300"><?= htmlspecialchars($item['property_label'] ?? '') ?></span>
    </td>

    <td class="px-6 py-4 hidden md:table-cell align-top">
        <span class="text-xs font-bold text-gray-700 dark:text-gray-300"><?= htmlspecialchars($item['service_name'] ?? '') ?></span>
    </td>

    <td class="px-6 py-4 whitespace-nowrap align-top">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest <?= $statusClass ?> border border-current/10">
            <?= htmlspecialchars($status) ?>
        </span>
    </td>

    <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell align-top text-right">
        <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300"><?= htmlspecialchars($item['created_at_formatted'] ?? 'N/A') ?></span>
    </td>

    <td class="px-6 py-4 whitespace-nowrap text-right align-top">
        <?php if ($isActive): ?>
            <button type="button" data-action="revoke-access-token"
                class="text-rose-600 dark:text-rose-400 hover:underline font-black text-[10px] uppercase tracking-widest">
                Revoke
            </button>
        <?php else: ?>
            <span class="text-gray-300 dark:text-gray-700 text-[10px] font-black uppercase tracking-widest">&mdash;</span>
        <?php endif; ?>
    </td>
</tr>
