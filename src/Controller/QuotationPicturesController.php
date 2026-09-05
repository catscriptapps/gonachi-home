<?php
// /src/Controller/QuotationPicturesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Quotation;
use App\Models\QuotationPic;
use Src\Service\ImageUploadService;

/**
 * Picture management for one quotation's project photos — up to
 * getMediaLimit() (12) images, owner-only, matching the legacy platform's
 * QuotationPicturesController.
 */
class QuotationPicturesController
{
    /**
     * @return array<int, array{entry_id:int, url:string, pos_index:int}>
     */
    public static function list(int $quotationId): array
    {
        $assetBase = getAssetBase();

        return QuotationPic::where('quotation_id', $quotationId)
            ->orderBy('pos_index')
            ->get()
            ->map(fn (QuotationPic $pic) => [
                'entry_id' => $pic->entry_id,
                'url' => $assetBase . 'images/uploads/quotations/' . $pic->pic_name,
                'pos_index' => $pic->pos_index,
            ])
            ->all();
    }

    /**
     * @param array $files $_FILES['images'] (multi-file format)
     * @return array{success: bool, message?: string, files?: array}
     */
    public static function store(int $quotationId, int $userId, array $files): array
    {
        $quote = Quotation::where('quotation_id', $quotationId)->where('orig_user_id', $userId)->first();

        if (!$quote) {
            return ['success' => false, 'message' => 'Quotation not found, or not yours to manage.'];
        }

        $limit = getMediaLimit();
        $existingCount = QuotationPic::where('quotation_id', $quotationId)->count();
        $incoming = count($files['tmp_name'] ?? []);

        if ($existingCount >= $limit) {
            return ['success' => false, 'message' => "This quotation already has the maximum of {$limit} pictures."];
        }

        if ($existingCount + $incoming > $limit) {
            return ['success' => false, 'message' => "Only " . ($limit - $existingCount) . " more picture(s) can be added (limit {$limit})."];
        }

        $uploadDir = __DIR__ . '/../../public/images/uploads/quotations/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $service = new ImageUploadService($uploadDir, 2000, 90);
        $assetBase = getAssetBase();
        $nextPos = $existingCount;

        $uploaded = $service->upload($files, function (array $uploadedFiles) use ($quotationId, &$nextPos, $assetBase) {
            $rows = [];
            foreach ($uploadedFiles as $file) {
                $pic = QuotationPic::create([
                    'quotation_id' => $quotationId,
                    'pic_name' => $file['fileName'],
                    'pos_index' => $nextPos++,
                ]);
                $rows[] = ['entry_id' => $pic->entry_id, 'url' => $assetBase . 'images/uploads/quotations/' . $file['fileName']];
            }
            return $rows;
        });

        if (empty($uploaded) || (isset($uploaded['success']) && $uploaded['success'] === false)) {
            return ['success' => false, 'message' => 'Upload failed.'];
        }

        return ['success' => true, 'files' => $uploaded];
    }

    public static function delete(int $picId, int $userId): array
    {
        $pic = QuotationPic::with('quotation')->find($picId);

        if (!$pic || !$pic->isOwnedBy($userId)) {
            return ['success' => false, 'message' => 'Picture not found, or not yours to manage.'];
        }

        $pic->delete();

        return ['success' => true];
    }
}
