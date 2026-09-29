<?php
// /src/Controller/AdvertsPicturesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Advert;
use App\Models\AdvertPic;
use Illuminate\Database\Capsule\Manager as Capsule;
use Src\Service\ImageUploadService;
use Src\Service\PendingUploadTracker;
use Src\Service\PictureOrderService;

/**
 * Picture management for one advert — up to getMediaLimit() (12) images,
 * owner-only, matching the legacy platform's AdvertsPicturesController.
 */
class AdvertsPicturesController
{
    /**
     * Only URLs under this path are trusted when attaching photos to an
     * advert via replacePhotos() — guards against a crafted payload
     * smuggling in an external URL. See advert-photo-upload.php, the only
     * writer of this path (besides the post-creation store() above it).
     */
    private const ALLOWED_UPLOAD_PATH = 'images/uploads/adverts/';

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

        // Fresh cardHtml so the grid behind the modal updates its thumbnail
        // live — no F5 needed to see the newly-added picture.
        $advert->load(['owner', 'cta', 'package', 'pictures']);

        return ['success' => true, 'files' => $uploaded, 'cardHtml' => AdvertsController::renderCard($advert, $userId)];
    }

    /**
     * Reconciles an advert's photos with the submitted (full desired) set —
     * used by the compose modal's Photos strip, where photos are uploaded
     * (via advert-photo-upload.php) before/while the rest of the form is
     * filled out, then attached all at once on save. An edit that didn't
     * touch photos resubmits the same URLs it already had, so this must
     * leave those rows (and their files) alone rather than deleting and
     * recreating them — AdvertPic's deleting hook unlinks the real file,
     * and recreating a row never re-writes it, so a naive
     * delete-everything-then-recreate would leave the new row pointing at a
     * file that was just deleted a moment earlier. Mirrors
     * SwapListingsController::replacePhotos().
     *
     * @param string[] $urls Already-uploaded URLs from advert-photo-upload.php.
     */
    public static function replacePhotos(Advert $advert, array $urls): void
    {
        $assetBase = getAssetBase();

        $desiredNames = [];
        foreach ($urls as $url) {
            $url = trim((string) $url);

            if ($url === '' || !str_starts_with($url, $assetBase)) {
                continue;
            }

            $relative = substr($url, strlen($assetBase));

            if (str_starts_with($relative, self::ALLOWED_UPLOAD_PATH)) {
                $desiredNames[] = basename($relative);
            }
        }

        // Server-side enforcement of the same 12-photo cap the compose
        // modal's client-side check uses (getMediaLimit()) — the client
        // check alone isn't authoritative.
        $desiredNames = array_slice($desiredNames, 0, getMediaLimit());

        Capsule::connection()->transaction(function () use ($advert, $desiredNames) {
            $existingPics = AdvertPic::where('advert_id', $advert->id)->get()->keyBy('pic_name');
            $keptNames = [];
            $position = 0;

            foreach ($desiredNames as $name) {
                $existing = $existingPics->get($name);

                if ($existing && !in_array($name, $keptNames, true)) {
                    $existing->pos_index = $position++;
                    $existing->save();
                    $keptNames[] = $name;
                    continue;
                }

                AdvertPic::create([
                    'advert_id' => $advert->id,
                    'pic_name' => $name,
                    'pos_index' => $position++,
                ]);
            }

            // Delete only pics that are no longer wanted — this is what
            // actually unlinks a truly-removed photo's file.
            foreach ($existingPics as $name => $pic) {
                if (!in_array($name, $keptNames, true)) {
                    $pic->delete();
                }
            }
        });

        // Every path in the final desired set is now backed by a real
        // AdvertPic row — no longer "pending" (see PendingUploadTracker).
        PendingUploadTracker::untrackAttached(array_map(
            fn (string $name) => self::ALLOWED_UPLOAD_PATH . $name,
            $desiredNames
        ));
    }

    /**
     * @param int[] $order Picture ids in the desired order.
     */
    public static function reorder(int $advertId, array $order, int $userId): array
    {
        $advert = Advert::where('id', $advertId)->where('user_id', $userId)->first();

        if (!$advert) {
            return ['success' => false, 'message' => 'Advert not found, or not yours to manage.'];
        }

        $pics = AdvertPic::where('advert_id', $advertId)->get();

        if (!PictureOrderService::apply($pics, $order, 'id', 'pos_index')) {
            return ['success' => false, 'message' => 'That picture order is out of date — please reopen the advert and try again.'];
        }

        // The card's thumbnail is the first picture, so it can change.
        $advert->load(['owner', 'cta', 'package', 'pictures']);

        return ['success' => true, 'cardHtml' => AdvertsController::renderCard($advert, $userId)];
    }

    public static function delete(int $picId, int $userId): array
    {
        $pic = AdvertPic::with('advert')->find($picId);

        if (!$pic || !$pic->advert || (int) $pic->advert->user_id !== $userId) {
            return ['success' => false, 'message' => 'Picture not found, or not yours to manage.'];
        }

        $advert = $pic->advert;
        $pic->delete();

        // Fresh cardHtml so the grid behind the modal updates its thumbnail
        // live — no F5 needed to see the picture is gone.
        $advert->load(['owner', 'cta', 'package', 'pictures']);

        return ['success' => true, 'cardHtml' => AdvertsController::renderCard($advert, $userId)];
    }
}
