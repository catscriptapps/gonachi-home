<?php
// /resources/views/components/contractor-avatar.php
//
// Renders a contractor's real uploaded photo if one exists, otherwise a
// deterministic colored initials card (Src\Utils\ContractorAvatar) so every
// contractor has a visually distinct "profile picture" even before they
// claim their listing. Included from the discovery feed card and the
// detail page header — each passes its own $avatarSizeClasses.
//
// @var \App\Models\Contractor $contractor
// @var string $assetBase
// @var string|null $avatarSizeClasses

use Src\Utils\ContractorAvatar;

$avatarSizeClasses = $avatarSizeClasses ?? 'h-14 w-14 text-base rounded-xl';
$hasAvatar = !empty($contractor->avatar_url);
$avatarFullUrl = $hasAvatar ? $assetBase . 'images/uploads/contractors/' . htmlspecialchars($contractor->avatar_url) : null;
?>
<div id="contractor-avatar-<?= $contractor->id ?>" class="<?= $avatarSizeClasses ?> overflow-hidden flex-shrink-0 flex items-center justify-center font-black text-white shadow-sm <?= $hasAvatar ? 'cursor-zoom-in' : '' ?>"
    <?= $hasAvatar ? 'data-img-src="' . $avatarFullUrl . '"' : 'style="' . ContractorAvatar::gradientStyle($contractor->business_name) . '"' ?>>
    <?php if ($hasAvatar): ?>
        <img src="<?= $avatarFullUrl ?>" alt="<?= htmlspecialchars($contractor->business_name) ?>" class="w-full h-full object-cover pointer-events-none" />
    <?php else: ?>
        <?= htmlspecialchars(ContractorAvatar::initials($contractor->business_name)) ?>
    <?php endif; ?>
</div>
