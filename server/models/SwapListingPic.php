<?php
// /server/models/SwapListingPic.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwapListingPic extends Model
{
    protected $table = 'swp_listing_pics';

    protected $fillable = [
        'listing_id',
        'file_path',
        'position',
    ];

    protected $casts = [
        'listing_id' => 'integer',
        'position' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(SwapListing::class, 'listing_id');
    }

    protected static function booted(): void
    {
        // Only unlinks real uploads (images/uploads/swap-listings/...) —
        // seeded demo listings point at the shared curated home-page photos
        // (images/home/...), which must never be deleted just because a
        // demo listing's photo row is removed.
        static::deleting(function (SwapListingPic $pic) {
            if (!str_starts_with($pic->file_path, 'images/uploads/swap-listings/')) {
                return;
            }

            $fullPath = dirname(__DIR__, 2) . '/public/' . $pic->file_path;
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        });
    }
}
