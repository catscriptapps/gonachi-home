<?php
// /src/Controller/ContractorClaimController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Contractor;
use App\Models\ContractorClaim;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ContractorClaimController
 * The "Claim This Profile" loop: a logged-in user claims an unclaimed
 * contractor listing, an admin approves/rejects it here (mirrors
 * LeadReviewController's moderation shape). Approval sets the contractor's
 * claim_status = claimed, which drives the "Verified" badge.
 */
class ContractorClaimController
{
    /**
     * @return array{success: bool, errors: string[]}
     */
    public static function submit(int $contractorId, int $userId, array $input): array
    {
        $contractor = Contractor::active()->find($contractorId);

        if (!$contractor) {
            return ['success' => false, 'errors' => ['Contractor not found.']];
        }

        if ($contractor->claim_status !== 'unclaimed') {
            return ['success' => false, 'errors' => ['This profile already has a claim in progress or has been claimed.']];
        }

        $contactPhone = trim((string) ($input['contact_phone'] ?? ''));
        if ($contactPhone === '') {
            return ['success' => false, 'errors' => ['A contact phone number is required.']];
        }

        ContractorClaim::create([
            'contractor_id' => $contractor->id,
            'user_id' => $userId,
            'message' => trim((string) ($input['message'] ?? '')) ?: null,
            'contact_phone' => $contactPhone,
            'status' => 'pending',
        ]);

        $contractor->claim_status = 'pending';
        $contractor->save();

        return ['success' => true, 'errors' => []];
    }

    public static function pending(int $perPage = 15): LengthAwarePaginator
    {
        return ContractorClaim::with(['contractor', 'user'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public static function approve(int $id): bool
    {
        $claim = ContractorClaim::where('status', 'pending')->find($id);
        if (!$claim) {
            return false;
        }

        $claim->status = 'approved';
        $claim->save();

        $contractor = Contractor::find($claim->contractor_id);
        if ($contractor) {
            $contractor->claimed_by_user_id = $claim->user_id;
            $contractor->claim_status = 'claimed';
            $contractor->save();
        }

        return true;
    }

    public static function reject(int $id): bool
    {
        $claim = ContractorClaim::where('status', 'pending')->find($id);
        if (!$claim) {
            return false;
        }

        $claim->status = 'rejected';
        $claim->save();

        $contractor = Contractor::find($claim->contractor_id);
        if ($contractor && $contractor->claim_status === 'pending') {
            $contractor->claim_status = 'unclaimed';
            $contractor->save();
        }

        return true;
    }
}
