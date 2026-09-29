<?php
// /src/Controller/ListingPicturesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Listing;
use App\Models\ListingPic;
use Illuminate\Database\Capsule\Manager as Capsule;
use Src\Service\ImageUploadService;
use Src\Service\PendingUploadTracker;
use Src\Service\PictureOrderService;

/**
 * Picture management for one listing's photos — up to getMediaLimit() (12)
 * images, owner-only, matching the legacy platform's ListingPicturesController
 * (and the same pattern already used for Adverts/Quotations pics here).
 */
class ListingPicturesController
{
    /**
     * Only URLs under this path are trusted when attaching photos to a
     * listing via replacePhotos() — guards against a crafted payload
     * smuggling in an external URL. See listing-photo-upload.php, the only
     * writer of this path (besides the post-creation store() above it).
     */
    private const ALLOWED_UPLOAD_PATH = 'images/uploads/listings/';

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

        // Fresh cardHtml so the grid behind the modal updates its thumbnail
        // live — no F5 needed to see the newly-added picture.
        $listing->load(ListingsController::EAGER);

        return ['success' => true, 'files' => $uploaded, 'cardHtml' => ListingsController::renderCard($listing, $userId)];
    }

    /**
     * Reconciles a listing's photos with the submitted (full desired) set —
     * used by the compose modal's Photos strip, where photos are uploaded
     * (via listing-photo-upload.php) before/while the rest of the form is
     * filled out, then attached all at once on save. An edit that didn't
     * touch photos resubmits the same URLs it already had, so this must
     * leave those rows (and their files) alone rather than deleting and
     * recreating them — ListingPic's deleting hook unlinks the real file,
     * and recreating a row never re-writes it, so a naive
     * delete-everything-then-recreate would leave the new row pointing at a
     * file that was just deleted a moment earlier. Mirrors
     * SwapListingsController::replacePhotos().
     *
     * @param string[] $urls Already-uploaded URLs from listing-photo-upload.php.
     */
    public static function replacePhotos(Listing $listing, array $urls): void
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

        Capsule::connection()->transaction(function () use ($listing, $desiredNames) {
            $existingPics = ListingPic::where('listing_id', $listing->listing_id)->get()->keyBy('pic_name');
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

                ListingPic::create([
                    'listing_id' => $listing->listing_id,
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
        // ListingPic row — no longer "pending" (see PendingUploadTracker).
        PendingUploadTracker::untrackAttached(array_map(
            fn (string $name) => self::ALLOWED_UPLOAD_PATH . $name,
            $desiredNames
        ));
    }

    /**
     * @param int[] $order Picture entry_ids in the desired order.
     */
    public static function reorder(int $listingId, array $order, int $userId): array
    {
        $listing = Listing::where('listing_id', $listingId)->where('orig_user_id', $userId)->first();

        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found, or not yours to manage.'];
        }

        $pics = ListingPic::where('listing_id', $listingId)->get();

        if (!PictureOrderService::apply($pics, $order, 'entry_id', 'pos_index')) {
            return ['success' => false, 'message' => 'That picture order is out of date — please reopen the listing and try again.'];
        }

        // The card's thumbnail is the first picture, so it can change.
        $listing->load(ListingsController::EAGER);

        return ['success' => true, 'cardHtml' => ListingsController::renderCard($listing, $userId)];
    }

    public static function delete(int $picId, int $userId): array
    {
        $pic = ListingPic::with('listing')->find($picId);

        if (!$pic || !$pic->isOwnedBy($userId)) {
            return ['success' => false, 'message' => 'Picture not found, or not yours to manage.'];
        }

        $listing = $pic->listing;
        $pic->delete();

        // Fresh cardHtml so the grid behind the modal updates its thumbnail
        // live — no F5 needed to see the picture is gone.
        $listing->load(ListingsController::EAGER);

        return ['success' => true, 'cardHtml' => ListingsController::renderCard($listing, $userId)];
    }
}
