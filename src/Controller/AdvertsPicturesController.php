<?php
// /src/Controller/AdvertsPicturesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Advert;
use App\Models\AdvertPic;
use Src\Service\ImageUploadService;

/**
 * Picture management for one advert — up to getMediaLimit() (12) images,
 * owner-only, matching the legacy platform's AdvertsPicturesController.
 */
class AdvertsPicturesController
{
    /**
     * @return array<int, array{entry_id:int, url:string, pos_index:int}>
     */
    public static function list(int $advertId): array
    {
        $assetBase = getAssetBase();

        return AdvertPic::where('advert_id', $advertId)
            ->orderBy('pos_index')
            ->get()
            ->map(fn(AdvertPic $pic) => [
                'entry_id' => $pic->id,
                'url' => $assetBase . 'images/uploads/adverts/' . $pic->pic_name,
                'pos_index' => $pic->pos_index,
            ])
            ->all();
    }

    /**
     * @param array $files $_FILES['images'] (multi-file format)
     * @return array{success: bool, message?: string, files?: array}
     */
    public static function store(int $advertId, int $userId, array $files): array
    {
        $advert = Advert::where('id', $advertId)->where('user_id', $userId)->first();

        if (!$advert) {
            return ['success' => false, 'message' => 'Advert not found, or not yours to manage.'];
        }

        $limit = getMediaLimit();
        $existingCount = AdvertPic::where('advert_id', $advertId)->count();
        $incoming = count($files['tmp_name'] ?? []);

        if ($existingCount >= $limit) {
            return ['success' => false, 'message' => "This advert already has the maximum of {$limit} pictures."];
        }

        if ($existingCount + $incoming > $limit) {
            return ['success' => false, 'message' => "Only " . ($limit - $existingCount) . " more picture(s) can be added (limit {$limit})."];
        }

        $uploadDir = __DIR__ . '/../../public/images/uploads/adverts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $service = new ImageUploadService($uploadDir, 2000, 90);
        $assetBase = getAssetBase();
        $nextPos = $existingCount;

        $uploaded = $service->upload($files, function (array $uploadedFiles) use ($advertId, &$nextPos, $assetBase) {
            $rows = [];
            foreach ($uploadedFiles as $file) {
                $pic = AdvertPic::create([
                    'advert_id' => $advertId,
                    'pic_name' => $file['fileName'],
                    'pos_index' => $nextPos++,
                ]);
                $rows[] = ['entry_id' => $pic->id, 'url' => $assetBase . 'images/uploads/adverts/' . $file['fileName']];
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
        $pic = AdvertPic::with('advert')->find($picId);

        if (!$pic || !$pic->advert || (int) $pic->advert->user_id !== $userId) {
            return ['success' => false, 'message' => 'Picture not found, or not yours to manage.'];
        }

        $pic->delete();

        return ['success' => true];
    }
}
