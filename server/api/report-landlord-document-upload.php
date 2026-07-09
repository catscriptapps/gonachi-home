<?php
// /server/api/report-landlord-document-upload.php
//
// Supporting-evidence (PDF-only) upload target for the Report A Landlord
// form. Unlike Building Pictures, these documents don't run through the
// shared upload modal's image-compression WorkerPool (it's image-specific —
// createImageBitmap() can't process a PDF) — report-landlord-page.js uploads
// each selected file here immediately via a plain multipart POST. The
// client-side "must be a PDF" check is a UX nicety only; this is the real
// gate, verifying actual file content rather than trusting the extension or
// the browser-supplied MIME type.

declare(strict_types=1);

use Src\Service\AuthService;

header('Content-Type: application/json');

if (!AuthService::userId()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in to upload documents.']);
    exit;
}

if (empty($_FILES['documents']) || empty($_FILES['documents']['tmp_name'][0])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No documents found.']);
    exit;
}

$uploadDir = realpath(__DIR__ . '/../../public/images/uploads/') . '/landlord-reports/documents/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$files = [];
$totalFiles = count($_FILES['documents']['tmp_name']);

for ($i = 0; $i < $totalFiles; $i++) {
    $tmpName = $_FILES['documents']['tmp_name'][$i] ?? null;
    $error = $_FILES['documents']['error'][$i] ?? UPLOAD_ERR_NO_FILE;

    if ($error !== UPLOAD_ERR_OK || !$tmpName || !is_uploaded_file($tmpName)) {
        continue;
    }

    // Verify actual file content — never trust the client-supplied mime/extension.
    $mimeType = finfo_file($finfo, $tmpName);
    if ($mimeType !== 'application/pdf') {
        continue;
    }

    $fileName = uniqid() . '-' . time() . '-' . ($i + 1) . '.pdf';
    $destination = $uploadDir . $fileName;

    if (move_uploaded_file($tmpName, $destination)) {
        $files[] = [
            'fileName' => $fileName,
            'url' => '/images/uploads/landlord-reports/documents/' . $fileName,
        ];
    }
}

finfo_close($finfo);

if (empty($files)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No valid PDF files were uploaded.']);
    exit;
}

echo json_encode(['success' => true, 'files' => $files]);
