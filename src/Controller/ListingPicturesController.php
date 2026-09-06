<?php
// /src/Controller/ListingPicturesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Listing;
use App\Models\ListingPic;
use Src\Service\ImageUploadService;

/**
 * Picture management for one listing's photos — up to getMediaLimit() (12)
 * images, owner-only, matching the legacy platform's ListingPicturesController
 * (and the same pattern already used for Adverts/Quotations pics here).
 */
class ListingPicturesController
{
    /**
     * @return array<int, array{entry_id:int, url:string, pos_index:int}>
     */
    public static function list(int $listingId): array
    {
        $assetBase = getAssetBase();

        return ListingPic::where('listing_id', $listingId)
            ->orderBy('pos_index')
            ->get()
            ->map(fn (ListingPic $pic) => [
                'entry_id' => $pic->entry_id,
                'url' => $assetBase . 'images/uploads/listings/' . $pic->pic_name,
                'pos_index' => $pic->pos_index,
            ])
            ->all();
    }

    /**
     * @param array $files $_FILES['images'] (multi-file format)
     * @return array{success: bool, message?: string, files?: array}
     */
    public static function store(int $listingId, int $userId, array $files): array
    {
        $listing = Listing::where('listing_id', $listingId)->where('orig_user_id', $userId)->first();

        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found, or not yours to manage.'];
        }

        $limit = getMediaLimit();
        $existingCount = ListingPic::where('listing_id', $listingId)->count();
        $incoming = count($files['tmp_name'] ?? []);

        if ($existingCount >= $limit) {
            return ['success' => false, 'message' => "This listing already has the maximum of {$limit} pictures."];
        }

        if ($existingCount + $incoming > $limit) {
            return ['success' => false, 'message' => "Only " . ($limit - $existingCount) . " more picture(s) can be added (limit {$limit})."];
        }

        $uploadDir = __DIR__ . '/../../public/images/uploads/listings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $service = new ImageUploadService($uploadDir, 2000, 90);
        $assetBase = getAssetBase();
        $nextPos = $existingCount;

        $uploaded = $service->upload($files, function (array $uploadedFiles) use ($listingId, &$nextPos, $assetBase) {
            $rows = [];
            foreach ($uploadedFiles as $file) {
                $pic = ListingPic::create([
                    'listing_id' => $listingId,
                    'pic_name' => $file['fileName'],
                    'pos_index' => $nextPos++,
                ]);
                $rows[] = ['entry_id' => $pic->entry_id, 'url' => $assetBase . 'images/uploads/listings/' . $file['fileName']];
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
        $pic = ListingPic::with('listing')->find($picId);

        if (!$pic || !$pic->isOwnedBy($userId)) {
            return ['success' => false, 'message' => 'Picture not found, or not yours to manage.'];
        }

        $pic->delete();

        return ['success' => true];
    }
}
