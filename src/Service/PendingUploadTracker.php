<?php
// /src/Service/PendingUploadTracker.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\AdvertPic;
use App\Models\ListingPic;
use App\Models\PendingPhotoUpload;
use App\Models\QuotationPic;
use App\Models\SwapListingPic;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Guarantees a compose-modal Photos strip (resources/js/utils/compose-photo-strip.js)
 * never leaves an uploaded-but-never-attached file behind on disk.
 *
 * Every file the four *-photo-upload.php pre-upload endpoints write gets a
 * tracking row here. It's removed the moment the file becomes genuinely
 * attached (a real AdvertPic/ListingPic/QuotationPic/SwapListingPic row
 * references it — see untrackAttached(), called from each type's
 * replacePhotos()), or the instant the client discards it (removed from the
 * strip before submit, or the whole compose modal is dismissed unsaved —
 * see discard(), called from server/api/discard-photo-upload.php). Anything
 * that outlives both of those paths (a crashed tab, a dropped connection) is
 * caught by sweep(), run periodically by scripts/cron/sweep-pending-uploads.php.
 */
class PendingUploadTracker
{
    /**
     * relative_path prefix => [model class, column storing the value to
     * compare against — the full relative path for Swap Marketplace
     * (file_path), or just the basename for the three Real Estate World
     * pic tables (pic_name)].
     */
    private const TYPE_MAP = [
        'images/uploads/adverts/' => [AdvertPic::class, 'pic_name', true],
        'images/uploads/listings/' => [ListingPic::class, 'pic_name', true],
        'images/uploads/quotations/' => [QuotationPic::class, 'pic_name', true],
        'images/uploads/swap-listings/' => [SwapListingPic::class, 'file_path', false],
    ];

    private static bool $checked = false;

    /**
     * Creates pending_photo_uploads on first use if it doesn't exist yet, so
     * an already-deployed database gets the table without a (data-wiping)
     * full reset. Fresh installs / resets create it via scripts/reset.
     */
    private static function ensureTable(): void
    {
        if (self::$checked) {
            return;
        }
        self::$checked = true;

        if (!Capsule::schema()->hasTable('pending_photo_uploads')) {
            require_once __DIR__ . '/../../scripts/reset/pending-photo-uploads.php';
            resetPendingPhotoUploadsTable();
        }
    }

    /**
     * Records one freshly-uploaded file as pending — called once per file
     * from each of the four pre-upload endpoints, right after it's written
     * to disk.
     */
    public static function track(int $userId, string $relativePath): void
    {
        self::ensureTable();

        PendingPhotoUpload::firstOrCreate(['relative_path' => $relativePath], ['user_id' => $userId]);
    }

    /**
     * Called from each type's replacePhotos() after it reconciles a
     * save — every path in the final desired set is now backed by a real
     * pic row, so it's no longer "pending" (only removes the tracking row;
     * never touches the file, which the pic table now owns).
     *
     * @param string[] $relativePaths
     */
    public static function untrackAttached(array $relativePaths): void
    {
        if (empty($relativePaths)) {
            return;
        }

        self::ensureTable();

        PendingPhotoUpload::whereIn('relative_path', $relativePaths)->delete();
    }

    /**
     * Deletes one not-yet-attached upload — either the user removed it from
     * the Photos strip before submitting, or the whole compose modal was
     * dismissed unsaved with it still in the strip. Refuses to touch a file
     * that's already attached (belt-and-braces: the client only ever calls
     * this for URLs it uploaded itself and never actually saved, but a
     * stale/replayed request — e.g. two tabs — must not be able to delete a
     * file another save has since legitimately attached).
     *
     * @return array{success: bool, message?: string}
     */
    public static function discard(int $userId, string $relativePath): array
    {
        self::ensureTable();

        $pending = PendingPhotoUpload::where('relative_path', $relativePath)
            ->where('user_id', $userId)
            ->first();

        if (!$pending) {
            // Already discarded, already attached-and-untracked, or never
            // tracked (e.g. a pre-existing photo from the edit prefill,
            // which the client never asks to discard anyway) — a no-op
            // either way, not an error the caller needs to react to.
            return ['success' => true];
        }

        if (self::isAttached($relativePath)) {
            $pending->delete();
            return ['success' => true];
        }

        $fullPath = dirname(__DIR__, 2) . '/public/' . $relativePath;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        $pending->delete();

        return ['success' => true];
    }

    /**
     * Deletes every tracked file older than $graceSeconds that still isn't
     * attached to anything — the safety net for uploads a client never got
     * to explicitly discard (closed tab, crashed browser, dropped network).
     * The grace period exists purely so this can't race a save that's still
     * in flight for a just-uploaded file.
     *
     * @return array{checked: int, deleted: int}
     */
    public static function sweep(int $graceSeconds = 3600): array
    {
        self::ensureTable();

        // Compares against the DATABASE's own clock (NOW()), not PHP's — a
        // PHP-computed cutoff would silently misfire the moment the two
        // clocks/timezones disagree (confirmed to happen in this deploy:
        // PHP and MySQL were three hours apart), which would flag every
        // just-uploaded file as already stale and delete it out from under
        // an in-progress compose session.
        $stale = PendingPhotoUpload::where('created_at', '<', Capsule::raw("(NOW() - INTERVAL {$graceSeconds} SECOND)"))->get();

        $deleted = 0;

        foreach ($stale as $pending) {
            if (self::isAttached($pending->relative_path)) {
                $pending->delete();
                continue;
            }

            $fullPath = dirname(__DIR__, 2) . '/public/' . $pending->relative_path;
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }

            $pending->delete();
            $deleted++;
        }

        return ['checked' => $stale->count(), 'deleted' => $deleted];
    }

    private static function isAttached(string $relativePath): bool
    {
        foreach (self::TYPE_MAP as $prefix => [$modelClass, $column, $useBasename]) {
            if (!str_starts_with($relativePath, $prefix)) {
                continue;
            }

            $needle = $useBasename ? basename($relativePath) : $relativePath;

            return $modelClass::where($column, $needle)->exists();
        }

        // An unrecognized path shouldn't be reachable (the four upload
        // endpoints only ever track their own known prefix), but fail safe
        // — never delete something this service doesn't recognize.
        return true;
    }
}
