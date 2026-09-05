<?php
// /resources/views/components/mentors/data-card.php
//
// One mentor card for the shared /mentors directory. Carries a data-*
// attribute payload (read by resources/js/utils/mentors/view-mentor-modal.js's
// JS counterpart) so clicking the card opens the shared view modal without
// a fetch.

/** @var array $data Built by MentorsController::buildItemArray() */
/** @var string $assetBase */

$ownerAvatarUrl = $data['owner_avatar'] ? $assetBase . 'images/uploads/avatars/' . htmlspecialchars($data['owner_avatar']) : null;
$skills = $data['skills'] ?: [];
?>
<div class="mentor-card-wrapper group relative flex flex-col bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition-all cursor-pointer view-mentor-trigger"
    data-encoded-id="<?= $data['encoded_id'] ?>"
    data-id="<?= (int) $data['id'] ?>"
    data-headline="<?= htmlspecialchars($data['headline']) ?>"
    data-bio="<?= htmlspecialchars((string) $data['bio']) ?>"
    data-city="<?= htmlspecialchars((string) $data['city']) ?>"
    data-country-id="<?= (int) $data['country_id'] ?>"
    data-country-name="<?= htmlspecialchars($data['country_name']) ?>"
    data-region-id="<?= (int) $data['region_id'] ?>"
    data-region-name="<?= htmlspecialchars($data['region_name']) ?>"
    data-target-type-id="<?= (int) $data['target_stakeholder_type_id'] ?>"
    data-target-user-type="<?= htmlspecialchars($data['target_stakeholder_type_name']) ?>"
    data-experience-years="<?= (int) $data['years_experience'] ?>"
    data-youtube-url="<?= htmlspecialchars((string) $data['youtube_url']) ?>"
    data-website-url="<?= htmlspecialchars((string) $data['website_url']) ?>"
    data-skills-json='<?= htmlspecialchars(json_encode($skills), ENT_QUOTES) ?>'
    data-created="<?= $data['created_at']?->format('M j, Y') ?>"
    data-updated="<?= $data['updated_at']?->format('M j, Y') ?>"
    data-owner-id="<?= (int) $data['owner_id'] ?>"
    data-owner-name="<?= htmlspecialchars($data['owner_name']) ?>"
    data-owner-avatar="<?= htmlspecialchars($ownerAvatarUrl ?? '') ?>"
    data-owner-initial="<?= htmlspecialchars($data['owner_initial']) ?>"
    data-owner-location="<?= htmlspecialchars($data['owner_location']) ?>"
    data-is-card-owner="<?= $data['is_card_owner'] ? '1' : '0' ?>">

    <div class="px-5 pt-5 flex justify-between items-start">
        <span class="inline-flex items-center rounded-full bg-teal-50 dark:bg-teal-950/40 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-teal-600 dark:text-teal-400 border border-teal-100 dark:border-teal-900/40">
            <?= htmlspecialchars($data['target_stakeholder_type_name']) ?>
        </span>

        <?php if ($data['is_card_owner']): ?>
            <div class="flex items-center gap-1.5 z-10" onclick="event.stopPropagation()">
                <button type="button" class="edit-mentor-btn p-1.5 rounded-lg bg-white/90 dark:bg-gray-800/90 text-gray-500 hover:text-teal-600 shadow-sm transition-colors" data-encoded-id="<?= $data['encoded_id'] ?>" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </button>
                <button type="button" class="delete-mentor-btn p-1.5 rounded-lg bg-white/90 dark:bg-gray-800/90 text-gray-500 hover:text-red-600 shadow-sm transition-colors" data-encoded-id="<?= $data['encoded_id'] ?>" title="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div class="px-5 pt-4">
        <div class="flex items-center gap-3 mb-3">
            <?php if ($ownerAvatarUrl): ?>
                <img src="<?= $ownerAvatarUrl ?>" alt="<?= htmlspecialchars($data['owner_name']) ?>" class="w-12 h-12 rounded-2xl object-cover shadow-sm">
            <?php else: ?>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-950/40 flex items-center justify-center text-lg font-black text-teal-500">
                    <?= htmlspecialchars($data['owner_initial']) ?>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h3 class="text-base font-bold text-gray-900 dark:text-white leading-snug truncate"><?= htmlspecialchars($data['owner_name']) ?></h3>
                <span class="text-[10px] font-black text-teal-600 dark:text-teal-400 uppercase tracking-widest"><?= (int) $data['years_experience'] ?>+ Years Experience</span>
            </div>
        </div>

        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-3"><?= htmlspecialchars($data['headline']) ?></span>

        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-3 mb-4"><?= htmlspecialchars((string) $data['bio'] ?: 'No bio provided.') ?></p>

        <div class="flex flex-wrap gap-1.5 border-t border-gray-50 dark:border-gray-800 pt-3">
            <?php foreach (array_slice($skills, 0, 3) as $skill): ?>
                <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400">#<?= htmlspecialchars(trim((string) $skill)) ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="mt-auto p-5 pt-4" onclick="event.stopPropagation()">
        <?php if ($data['is_card_owner']): ?>
            <button type="button" disabled class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 font-bold text-xs rounded-lg cursor-not-allowed">
                Your Profile
            </button>
        <?php else: ?>
            <button type="button" class="connect-mentor-trigger w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm active:scale-95"
                data-encoded-id="<?= $data['encoded_id'] ?>" data-id="<?= (int) $data['id'] ?>" data-owner-id="<?= (int) $data['owner_id'] ?>" data-owner-name="<?= htmlspecialchars($data['owner_name']) ?>" data-target-user-type="<?= htmlspecialchars($data['target_stakeholder_type_name']) ?>">
                Connect with Mentor
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </button>
        <?php endif; ?>
    </div>
</div>
