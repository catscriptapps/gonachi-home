<?php
// /resources/views/components/adverts/data-card.php
//
// One advert card for the public "Browse Adverts" feed and "My Adverts".
// Carries a large data-* attribute payload (read by
// resources/js/utils/adverts/view-advert-modal.php's JS counterpart) so
// clicking the card opens the shared view-advert modal without a fetch.

/** @var array $data Built by AdvertsController::buildItemArray() */
/** @var string $assetBase */
/** @var bool $isCardOwner */

$title = $data['title'];
$description = $data['description'];
$thumbnail = $data['thumbnail'] ? $assetBase . 'images/uploads/adverts/' . htmlspecialchars($data['thumbnail']) : null;
$videoUrl = $data['video_name'] ? $assetBase . 'videos/adverts/' . htmlspecialchars($data['video_name']) : null;
$ownerAvatarUrl = $data['owner_avatar'] ? $assetBase . 'images/uploads/avatars/' . htmlspecialchars($data['owner_avatar']) : null;

$landingUrl = trim((string) $data['landing_page_url']);
if ($landingUrl !== '' && !preg_match('#^https?://#i', $landingUrl)) {
    $landingUrl = 'https://' . $landingUrl;
}

$countryCount = in_array('ALL', $data['selected_countries'], true) ? 'All Countries' : count($data['selected_countries']) . ' ' . (count($data['selected_countries']) === 1 ? 'Country' : 'Countries');
$userTypeCount = in_array('ALL', $data['selected_user_types'], true) ? 'All User Types' : count($data['selected_user_types']) . ' ' . (count($data['selected_user_types']) === 1 ? 'User Type' : 'User Types');

$statusBadge = getStatusBadgeHtml($data['status']);
$viewsCountId = 'view-ad-' . $data['encoded_id'] . '-views-count';
$item = ['views' => $data['views']];

$hasAvatar = (bool) $ownerAvatarUrl;
$avatarUrl = $ownerAvatarUrl ?? '';
$ownerFullName = $data['owner_name'];
$initial = $data['owner_initial'];
$ownerLocation = $data['owner_location'];
?>
<div class="ad-card-wrapper group relative flex flex-col bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition-all cursor-pointer view-ad-trigger"
    data-encoded-id="<?= $data['encoded_id'] ?>"
    data-title="<?= htmlspecialchars($title) ?>"
    data-description="<?= htmlspecialchars($description) ?>"
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
    data-is-card-owner="<?= $isCardOwner ? '1' : '0' ?>">

    <div class="p-5 pb-0">
        <?php include __DIR__ . '/../ui/card-owner.php'; ?>
    </div>

    <?php if ($isCardOwner): ?>
        <div class="absolute top-4 right-4 flex items-center gap-1.5 z-10" onclick="event.stopPropagation()">
            <button type="button" class="edit-ad-btn p-1.5 rounded-lg bg-white/90 dark:bg-gray-800/90 text-gray-500 hover:text-primary-600 shadow-sm transition-colors" data-encoded-id="<?= $data['encoded_id'] ?>" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            </button>
            <button type="button" class="delete-ad-btn p-1.5 rounded-lg bg-white/90 dark:bg-gray-800/90 text-gray-500 hover:text-red-600 shadow-sm transition-colors" data-encoded-id="<?= $data['encoded_id'] ?>" title="Delete">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
        </div>
    <?php endif; ?>

    <div class="px-5 pt-3">
        <div class="mb-2">
            <?php include __DIR__ . '/../ui/status-badge-and-views-count.php'; ?>
        </div>
        <h3 class="text-base font-bold text-gray-900 dark:text-white leading-snug mb-1.5"><?= htmlspecialchars($title) ?></h3>
    </div>

    <?php if ($thumbnail): ?>
        <div class="mx-5 mb-3 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 h-40">
            <img src="<?= $thumbnail ?>" alt="<?= htmlspecialchars($title) ?>" class="w-full h-full object-cover">
        </div>
        <div class="px-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-3"><?= htmlspecialchars($description) ?></p>
        </div>
    <?php else: ?>
        <div class="px-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-3 mb-3"><?= htmlspecialchars($description) ?></p>
            <div class="flex flex-wrap gap-1.5 mb-3">
                <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-lg bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400"><?= $countryCount ?></span>
                <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-lg bg-secondary-50 dark:bg-secondary-950/40 text-secondary-600 dark:text-secondary-400"><?= $userTypeCount ?></span>
                <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-lg bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400"><?= htmlspecialchars($data['package_name']) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-auto p-5 pt-0" onclick="event.stopPropagation()">
        <?php if ($landingUrl !== ''): ?>
            <a href="<?= htmlspecialchars($landingUrl) ?>" target="_blank" rel="noopener noreferrer" class="block w-full text-center px-4 py-2.5 bg-gray-900 dark:bg-teal-600 hover:bg-gray-800 dark:hover:bg-teal-500 text-white font-bold text-xs rounded-lg transition-colors">
                <?= htmlspecialchars($data['cta_text']) ?>
            </a>
        <?php else: ?>
            <button type="button" disabled class="block w-full text-center px-4 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 font-bold text-xs rounded-lg cursor-not-allowed">
                No Link
            </button>
        <?php endif; ?>
    </div>
</div>
