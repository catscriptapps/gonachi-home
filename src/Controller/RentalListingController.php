<?php
// /src/Controller/RentalListingController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\LandlordRecord;
use App\Models\PropertyRecord;
use App\Models\RentalListing;
use App\Models\RentalListingPhoto;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * RentalListingController
 * "List A Property For Rent" contribution loop — mirrors
 * LandlordDirectoryController::submitReport() exactly: same
 * normalize/find-or-create landlord + property pattern, so a listing for an
 * address that already has landlord reports attaches to that same
 * PropertyRecord (real confidence score via
 * LandlordDirectoryController::confidenceScore(), not a fabricated one).
 * Listings are held as pending_review until an admin approves them via
 * /rental-listing-review — see RentalListingReviewController.
 */
class RentalListingController
{
    private const REQUIRED_FIELDS = ['address', 'landlord_name', 'area', 'rent_amount'];

    /**
     * Only URLs under this path are trusted when attaching photos — same
     * guard as LandlordDirectoryController::ALLOWED_UPLOAD_PATH.
     */
    private const ALLOWED_UPLOAD_PATH = 'images/uploads/rental-listings/';

    /**
     * @param array $input Decoded JSON body: address, landlord_name, area, property_type,
     *                      bedrooms, rent_amount, rent_period, description, photo_urls[].
     * @return array{success: bool, errors: string[]}
     */
    public static function submitListing(array $input, int $userId): array
    {
        $errors = [];
        foreach (self::REQUIRED_FIELDS as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                $errors[] = "The {$field} field is required.";
            }
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $address = trim((string) $input['address']);
        $landlordName = trim((string) $input['landlord_name']);

        $landlord = LandlordRecord::firstOrCreate(
            ['normalized_name' => self::normalize($landlordName)],
            ['name' => $landlordName]
        );

        $property = PropertyRecord::firstOrCreate(
            ['landlord_id' => $landlord->id, 'normalized_address' => self::normalize($address)],
            [
                'address' => $address,
                'property_type' => trim((string) ($input['property_type'] ?? '')) ?: null,
            ]
        );

        $rentAmount = trim((string) $input['rent_amount']);
        $rentAmount = $rentAmount === '' ? null : (float) preg_replace('/[^0-9.]/', '', $rentAmount);

        $bedrooms = trim((string) ($input['bedrooms'] ?? ''));
        $bedrooms = $bedrooms === '' ? null : (int) $bedrooms;

        $rentPeriod = (string) ($input['rent_period'] ?? 'year');
        $rentPeriod = in_array($rentPeriod, ['year', 'month'], true) ? $rentPeriod : 'year';

        $listing = RentalListing::create([
            'property_id' => $property->id,
            'landlord_id' => $landlord->id,
            'user_id' => $userId,
            'area' => trim((string) $input['area']),
            'bedrooms' => $bedrooms,
            'property_type' => trim((string) ($input['property_type'] ?? '')) ?: null,
            'rent_amount' => $rentAmount,
            'rent_period' => $rentPeriod,
            'description' => trim((string) ($input['description'] ?? '')) ?: null,
            'status' => 'pending_review',
        ]);

        self::attachPhotos($listing, $input['photo_urls'] ?? []);

        return ['success' => true, 'errors' => []];
    }

    /**
     * Most recently published listings, for the Rental Opportunities page.
     *
     * @return \Illuminate\Support\Collection<int, RentalListing>
     */
    public static function recentPublished(int $limit = 6)
    {
        return RentalListing::with(['property', 'landlord'])
            ->published()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Published listings, optionally filtered to one area, paginated for
     * the dedicated Rental Opportunities page.
     */
    public static function browse(?string $area = null, int $perPage = 9): LengthAwarePaginator
    {
        $query = RentalListing::with(['property', 'landlord'])->published();

        if ($area !== null && $area !== '') {
            $query->where('area', $area);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public static function totalPublished(): int
    {
        return RentalListing::published()->count();
    }

    /**
     * Top areas by published listing count — backs both the landing page's
     * "Rental Opportunities" teaser and the dedicated page's header pills.
     *
     * @return array<int, array{area: string, count: int}>
     */
    public static function countsByArea(int $limit = 3): array
    {
        return RentalListing::published()
            ->selectRaw('area, COUNT(*) as listing_count')
            ->groupBy('area')
            ->orderByDesc('listing_count')
            ->limit($limit)
            ->get()
            ->map(fn($row) => ['area' => $row->area, 'count' => (int) $row->listing_count])
            ->all();
    }

    /**
     * @param string[] $urls Already-uploaded URLs — see rental-listing-photo-upload.php.
     */
    private static function attachPhotos(RentalListing $listing, array $urls): void
    {
        $assetBase = getAssetBase();

        foreach ($urls as $url) {
            $url = trim((string) $url);

            if ($url === '' || !str_starts_with($url, $assetBase)) {
                continue;
            }

            $relative = substr($url, strlen($assetBase));

            if (!str_starts_with($relative, self::ALLOWED_UPLOAD_PATH)) {
                continue;
            }

            RentalListingPhoto::create([
                'listing_id' => $listing->id,
                'file_path' => $relative,
            ]);
        }
    }

    private static function normalize(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }
}
