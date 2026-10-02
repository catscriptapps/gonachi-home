<?php
// /src/Controller/ReviewController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\User;
use Src\Service\ReviewService;
use Src\Service\TenancyService;
use Src\Utils\CountryScope;

/**
 * ReviewController
 * Thin HTTP-facing entry points for the two review directions — "Review A
 * Landlord" (submitLandlordReview, reviewer is the tenant) and "Review A
 * Tenant" (submitTenantReview, reviewer is the landlord), keeping the same
 * naming convention as the old report-landlord/report-tenant flow. Each
 * identifies/creates the shared Tenancy (TenancyService::findOrCreate())
 * then hands off to ReviewService::submit() for validation, persistence,
 * and the double-blind release check.
 */
class ReviewController
{
    /**
     * @param array $input landlord_name, address (optional), property_type
     *                      (optional), criteria[] ({criterion_id,stars,is_na}),
     *                      comment (optional), tags[] (optional tag_keys).
     * @return array{success:bool, message?:string, errors?:string[]}
     */
    public static function submitLandlordReview(array $input, int $reviewerUserId, string $countryCode): array
    {
        return self::submit($input, $reviewerUserId, $countryCode, initiatorRole: 'tenant', revieweeType: 'landlord');
    }

    /**
     * @param array $input tenant_name, address (optional), property_type
     *                      (optional), criteria[], comment, tags[].
     */
    public static function submitTenantReview(array $input, int $reviewerUserId, string $countryCode): array
    {
        return self::submit($input, $reviewerUserId, $countryCode, initiatorRole: 'landlord', revieweeType: 'tenant');
    }

    private static function submit(array $input, int $reviewerUserId, string $countryCode, string $initiatorRole, string $revieweeType): array
    {
        $otherPartyName = trim((string) ($input['other_party_name'] ?? ''));
        if ($otherPartyName === '') {
            $label = $revieweeType === 'landlord' ? 'landlord' : 'tenant';
            return ['success' => false, 'errors' => ["The {$label} name field is required."]];
        }

        $reviewer = User::find($reviewerUserId);
        $reviewerName = $reviewer ? trim((string) $reviewer->full_name) : '';
        if ($reviewerName === '') {
            return ['success' => false, 'errors' => ['Your account needs a name on file before submitting a review.']];
        }

        $landlordName = $initiatorRole === 'landlord' ? $reviewerName : $otherPartyName;
        $tenantName = $initiatorRole === 'tenant' ? $reviewerName : $otherPartyName;

        $countryId = CountryScope::idFor($countryCode);

        $tenancy = TenancyService::findOrCreate(
            $reviewerUserId,
            $initiatorRole,
            $landlordName,
            $tenantName,
            trim((string) ($input['address'] ?? '')) ?: null,
            trim((string) ($input['property_type'] ?? '')) ?: null,
            $countryId
        );

        $criteria = is_array($input['criteria'] ?? null) ? $input['criteria'] : [];
        $tags = is_array($input['tags'] ?? null) ? $input['tags'] : [];
        $comment = $input['comment'] ?? null;

        return ReviewService::submit($tenancy, $reviewerUserId, $revieweeType, $criteria, $comment, $tags);
    }
}
