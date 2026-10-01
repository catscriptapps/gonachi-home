<?php
// /server/api/messages-unread-count.php
//
// Lightweight poll target for the header "Messages" badge — mirrors
// chat-admin-unread-count.php exactly. Kept separate from the main
// server/api/messages.php endpoint (which already returns unread_count
// alongside a full folder listing) since the badge needs to poll every few
// seconds from EVERY admin page, not just while the Messages inbox itself
// is open — a full folder query on every poll would be wasteful.

declare(strict_types=1);

use Src\Controller\MessagesController;
use Src\Service\AuthService;

header('Content-Type: application/json');

if (!AuthService::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

echo json_encode(['success' => true, 'count' => MessagesController::getUnreadCount()]);
