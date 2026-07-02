<?php
// /resources/views/pages/notifications.php

declare(strict_types=1);

/**
 * @var bool $isLoggedIn Whether the user is logged in
 */

use Src\Controller\NotificationsController;
use App\Models\Notification;

if ($isLoggedIn) {

    // 1. Fetch Data from Controller
    $notifications = NotificationsController::getLatest(20);
    $unreadCount = NotificationsController::getUnreadCount();
    $breakdown = NotificationsController::getUnreadBreakdown();

    // 2. Helper to safely get the count for a specific category from the DB breakdown
    $getCatCount = fn($type) => $breakdown[strtoupper($type)] ?? 0;

    // 3. Define Sidebar Filters with HeroIcons
    $filters = [
        ['All Alerts', '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />', $unreadCount],
        ['Listings', NotificationsController::getMetadata(Notification::TYPE_LISTING)['icon'], $getCatCount(Notification::TYPE_LISTING)],
        ['System', NotificationsController::getMetadata(Notification::TYPE_SYSTEM)['icon'], $getCatCount(Notification::TYPE_SYSTEM)],
    ];

    $pageIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>';
?>

    <div id="notifications-page" class="min-h-screen bg-gray-50 dark:bg-gray-950 font-sans pb-20 transition-colors duration-300">
        <div class="max-w-7xl mx-auto pt-12 px-4 sm:px-6 lg:px-8">

            <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6" data-aos="fade-down">
                <div class="flex items-start gap-5">
                    <div class="mt-4 hidden sm:flex w-20 h-20 rounded-[2rem] bg-gradient-to-br from-primary-500 to-secondary-600 items-center justify-center text-white shadow-2xl shadow-primary-500/20 rotate-3 hover:rotate-0 transition-transform duration-500">
                        <?= preg_replace('/(<svg[^>]*)(>)/i', '$1 class="w-10 h-10 animate-pulse"$2', $pageIcon) ?>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-500/10 text-primary-500 text-[10px] font-black uppercase tracking-widest mb-3 border border-primary-500/20">
                            Communication Hub
                        </div>
                        <h3 class="text-4xl sm:text-6xl font-black text-secondary-900 dark:text-white tracking-tighter leading-none">
                            Notifications <?= $unreadCount > 0 ? "<span class='text-primary-500 text-2xl sm:text-3xl'>( $unreadCount )</span>" : '' ?>
                        </h3>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button id="clear-notifications" class="hidden px-5 py-3 rounded-xl bg-white dark:bg-white/5 text-gray-500 text-[10px] font-black uppercase border border-gray-200 dark:border-white/10 hover:bg-gray-50 transition-all">Clear History</button>
                    <button id="mark-all-read" class="hidden px-5 py-3 rounded-xl bg-secondary-900 text-white text-[10px] font-black uppercase shadow-lg shadow-secondary-900/30 hover:bg-secondary-800 transition-all">Mark All As Read</button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 relative">
                <div class="absolute -left-20 top-40 opacity-[0.03] dark:opacity-[0.05] pointer-events-none -rotate-12 hidden xl:block">
                    <?= preg_replace('/(<svg[^>]*)(>)/i', '$1 class="w-96 h-96"$2', $pageIcon) ?>
                </div>

                <aside class="w-full lg:w-72 space-y-2 relative z-10" data-aos="fade-right">
                    <?php
                    $currentFilter = $_GET['filter'] ?? 'all';

                    foreach ($filters as $i => $filter):
                        $slug = strtolower(explode(' ', $filter[0])[0]);
                        $isActive = ($currentFilter === $slug);
                    ?>
                        <button class="w-full group flex items-center justify-between px-6 py-4 rounded-2xl transition-all duration-300 <?= $isActive ? 'bg-primary-600 text-white shadow-xl shadow-primary-600/20' : 'text-gray-500 hover:bg-white dark:hover:bg-white/5 border border-transparent hover:border-gray-100 dark:hover:border-white/10' ?>">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 opacity-70 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <?= $filter[1] ?>
                                </svg>
                                <span class="font-black text-[11px] uppercase tracking-[0.15em]"><?= $filter[0] ?></span>
                            </div>
                            <?php if ($filter[2] !== null): ?>
                                <span class="text-[10px] font-black px-2 py-0.5 rounded-lg transition-colors <?= $isActive ? 'bg-white/20 text-white' : ($filter[2] > 0 ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-white/5 text-gray-400') ?>">
                                    <?= $filter[2] ?>
                                </span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </aside>

                <main class="flex-1 space-y-4 relative z-10" data-aos="fade-left" data-aos-delay="200">
                    <?php if ($notifications->isEmpty()): ?>
                        <div class="text-center py-20 bg-white dark:bg-gray-900/50 rounded-[3rem] border border-dashed border-gray-300 dark:border-white/10">
                            <p class="text-gray-400 font-black uppercase tracking-widest italic">Your inbox is crystal clear.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $note):
                            $meta = NotificationsController::getMetadata($note->type);
                            $sender = $note->sender;
                            $senderUserTypesJson = getUserRoles($sender);

                            // Original declarations restored 🛠️
                            $senderName = $sender ? trim($sender->first_name . ' ' . $sender->last_name) : "System";
                            $senderHandle = $sender ? "@" . $sender->username : "System";
                            $senderInitial = strtoupper(substr($sender->first_name ?? 'S', 0, 1));
                            $senderAvatar = !empty($sender->avatar_url) ? getAssetBase() . 'images/uploads/avatars/' . $sender->avatar_url : '';
                            $senderLocation = $sender ?
                                ($sender->city ?? 'Remote') . ' - ' .
                                ($sender->region->region ?? 'Unknown Region') . ', ' .
                                ($sender->country->country ?? 'Unknown Country') : 'Unknown Location';
                        ?>
                            <div class="group relative bg-white dark:bg-gray-900/50 rounded-[2rem] p-5 sm:p-6 shadow-sm border-l-4 <?= $note->is_read ? 'border-l-transparent opacity-75' : 'border-l-primary-500' ?> border border-gray-100 dark:border-white/5 hover:shadow-xl hover:-translate-y-1 transition-all">
                                <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-6">
                                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-<?= $meta['color'] ?>-500/10 flex items-center justify-center text-<?= $meta['color'] ?>-500 shrink-0">
                                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <?= $meta['icon'] ?>
                                        </svg>
                                    </div>

                                    <div class="flex-1 w-full">
                                        <div class="flex justify-between items-start mb-1 gap-2">
                                            <h3 class="text-base sm:text-lg font-black text-secondary-900 dark:text-white uppercase tracking-tighter truncate">
                                                <?= htmlspecialchars($note->subject) ?>
                                            </h3>
                                            <span class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-widest shrink-0 mt-1">
                                                <?= $note->created_at->diffForHumans(null, true) ?>
                                            </span>
                                        </div>

                                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed mb-4 line-clamp-2">
                                            <span class="text-secondary-900 dark:text-white font-black italic mr-1"><?= $senderHandle ?></span>
                                            <?= htmlspecialchars($note->notification_message) ?>
                                        </p>

                                        <div class="flex flex-wrap gap-2">
                                            <button
                                                data-action="view-notification"
                                                data-id="<?= $note->id ?>"
                                                data-type="<?= $note->type ?>"
                                                data-target-id="<?= $note->target_id ?>"
                                                data-status="<?= $note->target_status ?>"
                                                data-sender-name="<?= htmlspecialchars($senderName) ?>"
                                                data-subject="<?= htmlspecialchars($note->subject) ?>"
                                                data-sender-avatar="<?= htmlspecialchars($senderAvatar) ?>"
                                                data-sender-initial="<?= $senderInitial ?>"
                                                data-sender-location="<?= htmlspecialchars($senderLocation) ?>"
                                                data-sender-user-types='<?= $senderUserTypesJson ?>'
                                                data-message="<?= htmlspecialchars($note->notification_message) ?>"
                                                data-context-title="<?= htmlspecialchars($note->context_title ?? 'Notification Detail') ?>"
                                                data-context-info="<?= htmlspecialchars($note->context_info ?? '') ?>"
                                                data-target-user-type="<?= htmlspecialchars($note->target_user_type ?? '') ?>"
                                                data-receiver-name="<?= htmlspecialchars($note->receiver_full_name ?? 'You') ?>"

                                                <?php if (!empty($note->listing_attrs)): ?>
                                                <?php foreach ($note->listing_attrs as $attr => $val): ?>
                                                data-<?= $attr ?>="<?= htmlspecialchars((string)$val) ?>"
                                                <?php endforeach; ?>
                                                <?php endif; ?>
                                                class=" px-5 py-2.5 bg-secondary-900 dark:bg-secondary-700 text-white text-[10px] font-black uppercase rounded-xl hover:bg-black dark:hover:bg-secondary-900 transition-all">View Details
                                            </button>

                                            <button
                                                data-action="delete-notification"
                                                data-id="<?= $note->id ?>"
                                                class="p-2.5 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-xl transition-all group shrink-0"
                                                title="Delete Notification">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </main>
            </div>
        </div>

        <?php include __DIR__ . '/../components/notifications/notification-modal.php'; ?>
    </div>
<?php
} else {
    include __DIR__ . '/auth-required.php';
}
