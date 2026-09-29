<?php
// /server/models/PendingPhotoUpload.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tracks a photo file from the moment a compose-modal Photos strip uploads
 * it (via one of the *-photo-upload.php pre-upload endpoints) until it's
 * either attached to a real record (a row appears in the matching
 * AdvertPic/ListingPic/QuotationPic/SwapListingPic table — see
 * Src\Service\PendingUploadTracker::untrackAttached()) or discarded. A row
 * that outlives its owner ever finishing that compose flow (closed tab,
 * crashed browser, network drop) is what
 * scripts/cron/sweep-pending-uploads.php cleans up.
 */
class PendingPhotoUpload extends Model
{
    protected $table = 'pending_photo_uploads';

    public $timestamps = false;

    protected $fillable = ['user_id', 'relative_path'];

    protected $casts = [
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];
}
