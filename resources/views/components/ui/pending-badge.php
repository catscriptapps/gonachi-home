<?php
// /resources/views/components/ui/pending-badge.php
//
// Owner-only "N pending messages" pill for cards whose owner can be
// contacted via "Connect with Owner" (Swap listings, Real Estate World
// quotations and mentors). Always rendered for owners — hidden at zero — so
// utils/pending-badge.js can reveal it live when the count changes.
//
// @var int    $pendingCount   Pending (undecided) messages on this item.
// @var string $pendingNoun    Singular noun, e.g. 'Message' or 'Request'.
// @var string $pendingBgClass Full literal Tailwind bg class (e.g. 'bg-teal-600')
//                             — passed in whole so Tailwind's scanner keeps it.

$pendingCount = (int) ($pendingCount ?? 0);
$pendingNoun = (string) ($pendingNoun ?? 'Message');
$pendingBgClass = (string) ($pendingBgClass ?? 'bg-teal-600');
?>
<span data-pending-badge data-noun="<?= htmlspecialchars($pendingNoun) ?>" title="Waiting for your response"
    class="<?= $pendingCount > 0 ? 'inline-flex' : 'hidden' ?> items-center gap-1 whitespace-nowrap rounded-full <?= htmlspecialchars($pendingBgClass) ?> px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-white shadow-sm">
    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a2 2 0 001.995-1.85L12 16H8a2 2 0 001.85 1.995L10 18z"/></svg>
    <span data-pending-count><?= $pendingCount ?></span>
    <span data-pending-label>Pending <?= htmlspecialchars($pendingNoun) ?><?= $pendingCount === 1 ? '' : 's' ?></span>
</span>
