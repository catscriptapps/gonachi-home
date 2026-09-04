<?php
// /resources/views/components/adverts/data-row.php
//
// One row in the admin Adverts moderation table. Carries the same data-*
// payload as data-card.php so the shared view-advert modal works from
// either context.

/** @var array $data Built by AdvertsController::buildItemArray() */
/** @var string $assetBase */

$ownerAvatarUrl = $data['owner_avatar'] ? $assetBase . 'images/uploads/avatars/' . htmlspecialchars($data['owner_avatar']) : null;
$videoUrl = $data['video_name'] ? $assetBase . 'videos/adverts/' . htmlspecialchars($data['video_name']) : null;
$landingUrl = trim((string) $data['landing_page_url']);
if ($landingUrl !== '' && !preg_match('#^https?://#i', $landingUrl)) {
    $landingUrl = 'https://' . $landingUrl;
}
$statusBadge = getStatusBadgeHtml($data['status']);
?>
<tr id="ad-row-<?= $data['encoded_id'] ?>" class="ad-row view-ad-trigger hover:bg-gray-50 dark:hover:bg-gray-800/40 cursor-pointer transition-colors"
    data-encoded-id="<?= $data['encoded_id'] ?>"
    data-title="<?= htmlspecialchars($data['title']) ?>"
    data-description="<?= htmlspecialchars($data['description']) ?>"
    data-call-to-action-id="<?= (int) ($data['cta_id'] ?? 0) ?>"
    data-call-to-action="<?= htmlspecialchars($data['cta_text']) ?>"
    data-keywords="<?= htmlspecialchars((string) $data['keywords']) ?>"
    data-landing-page-url="<?= htmlspecialchars($landingUrl) ?>"
    data-selected-countries='<?= htmlspecialchars(json_encode($data['selected_countries']), ENT_QUOTES) ?>'
    data-country-names='<?= htmlspecialchars(json_encode($data['country_names']), ENT_QUOTES) ?>'
    data-selected-user-types='<?= htmlspecialchars(json_encode($data['selected_user_types']), ENT_QUOTES) ?>'
    data-user-type-names='<?= htmlspecialchars(json_encode($data['user_type_names']), ENT_QUOTES) ?>'
    data-advert-package="<?= (int) $data['package_id'] ?>"
    data-advert-package-name="<?= htmlspecialchars($data['package_name']) ?>"
    data-advert-package-description="<?= htmlspecialchars($data['package_description']) ?>"
    data-advert-package-icon="<?= htmlspecialchars($data['package_icon']) ?>"
    data-status="<?= htmlspecialchars($data['status']) ?>"
    data-joined="<?= $data['created_at']?->format('M j, Y') ?>"
    data-updated="<?= $data['updated_at']?->format('M j, Y') ?>"
    data-views-count="<?= (int) $data['views'] ?>"
    data-owner-id="<?= (int) $data['owner_id'] ?>"
    data-owner-name="<?= htmlspecialchars($data['owner_name']) ?>"
    data-owner-avatar="<?= htmlspecialchars($ownerAvatarUrl ?? '') ?>"
    data-owner-initial="<?= htmlspecialchars($data['owner_initial']) ?>"
    data-owner-location="<?= htmlspecialchars($data['owner_location']) ?>"
    data-owner-user-types='<?= htmlspecialchars(json_encode($data['owner_user_types']), ENT_QUOTES) ?>'
    data-video-name="<?= htmlspecialchars((string) $data['video_name']) ?>"
    data-video-url="<?= htmlspecialchars($videoUrl ?? '') ?>"
    data-is-card-owner="0">

    <td class="px-4 py-3">
        <div class="flex items-center gap-3">
            <?php if ($data['thumbnail']): ?>
                <img src="<?= $assetBase ?>images/uploads/adverts/<?= htmlspecialchars($data['thumbnail']) ?>" class="w-10 h-10 rounded-lg object-cover border border-gray-100 dark:border-gray-800 flex-shrink-0">
            <?php else: ?>
                <div class="w-10 h-10 rounded-lg bg-teal-50 dark:bg-teal-950/40 flex items-center justify-center text-teal-600 dark:text-teal-400 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <p class="text-sm font-bold text-gray-900 dark:text-white truncate max-w-[16rem]"><?= htmlspecialchars($data['title']) ?></p>
                <p class="text-xs text-gray-400 truncate max-w-[16rem]"><?= (int) $data['views'] ?> views</p>
            </div>
        </div>
    </td>
    <td class="px-4 py-3">
        <div class="flex items-center gap-2">
            <?php if ($ownerAvatarUrl): ?>
                <img src="<?= $ownerAvatarUrl ?>" class="w-6 h-6 rounded-full object-cover">
            <?php else: ?>
                <div class="w-6 h-6 rounded-full bg-primary-600 flex items-center justify-center text-white text-[10px] font-bold"><?= htmlspecialchars($data['owner_initial']) ?></div>
            <?php endif; ?>
            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300"><?= htmlspecialchars($data['owner_name']) ?></span>
        </div>
    </td>
    <td class="px-4 py-3">
        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded-lg bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400"><?= htmlspecialchars($data['package_name']) ?></span>
    </td>
    <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400"><?= $data['created_at']?->format('M j, Y') ?></td>
    <td class="px-4 py-3"><?= $statusBadge ?></td>
</tr>
