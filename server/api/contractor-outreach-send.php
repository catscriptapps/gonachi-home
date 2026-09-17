<?php
// /server/api/contractor-outreach-send.php
//
// Admin-triggered SMS/email outreach to one or more unclaimed contractors
// (see Src\Service\ContractorOutreachService, Src\Service\SmsService,
// Src\Service\MailService). {contractor_ids} is always an array — a single
// send from the profile row and a bulk send from "Send to All (This Page)"
// both go through this same endpoint. {message} accepts {business_name}/
// {claim_url} tokens (see ContractorOutreachService::personalize()), so one
// edited template still reads as addressed to each business individually.

declare(strict_types=1);

use App\Models\Contractor;
use Src\Service\AuthService;
use Src\Service\ContractorOutreachService;

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'messages' => ['Method not allowed']]);
    exit;
}

if (!AuthService::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'messages' => ['Admins only.']]);
    exit;
}

$adminUserId = AuthService::userId();
$input = json_decode(file_get_contents('php://input'), true) ?: [];

$channel = $input['channel'] ?? '';
if (!in_array($channel, ['sms', 'email'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'messages' => ['Invalid channel.']]);
    exit;
}

$contractorIds = array_map('intval', (array) ($input['contractor_ids'] ?? []));
$contractorIds = array_filter($contractorIds, fn($id) => $id > 0);

if (empty($contractorIds)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'messages' => ['No contractors selected.']]);
    exit;
}

$message = trim((string) ($input['message'] ?? ''));
$subject = trim((string) ($input['subject'] ?? ''));

if ($message === '' || ($channel === 'email' && $subject === '')) {
    http_response_code(400);
    echo json_encode(['success' => false, 'messages' => ['A message' . ($channel === 'email' ? ' and subject are' : ' is') . ' required.']]);
    exit;
}

$contractors = Contractor::whereIn('id', $contractorIds)->get();

$sent = 0;
$failed = 0;
$failures = [];

foreach ($contractors as $contractor) {
    $personalizedMessage = ContractorOutreachService::personalize($contractor, $message);

    $result = $channel === 'sms'
        ? ContractorOutreachService::sendSms($contractor, $personalizedMessage, $adminUserId)
        : ContractorOutreachService::sendEmail($contractor, ContractorOutreachService::personalize($contractor, $subject), $personalizedMessage, $adminUserId);

    if ($result['success']) {
        $sent++;
    } else {
        $failed++;
        $failures[] = "{$contractor->business_name}: {$result['message']}";
    }
}

echo json_encode([
    'success' => $sent > 0,
    'sent' => $sent,
    'failed' => $failed,
    'messages' => $failed > 0 ? array_slice($failures, 0, 5) : ["Sent to {$sent} contractor" . ($sent === 1 ? '' : 's') . "."],
]);
