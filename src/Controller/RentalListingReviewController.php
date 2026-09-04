<?php
// /src/Controller/RentalListingReviewController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\RentalListing;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * RentalListingReviewController
 * Admin-only moderation queue for submitted rental listings (status =
 * pending_review). Exact mirror of LandlordReportReviewController.
 */
class RentalListingReviewController
{
    public static function pending(int $perPage = 15): LengthAwarePaginator
    {
        return RentalListing::with(['property', 'landlord', 'user', 'photos'])
            ->where('status', 'pending_review')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public static function approve(int $id): bool
    {
        $listing = RentalListing::where('status', 'pending_review')->find($id);
        if (!$listing) {
            return false;
        }

        $listing->status = 'published';
        return $listing->save();
    }

    public static function reject(int $id): bool
    {
        $listing = RentalListing::where('status', 'pending_review')->find($id);
        if (!$listing) {
            return false;
        }

        $listing->status = 'rejected';
        return $listing->save();
    }
}
