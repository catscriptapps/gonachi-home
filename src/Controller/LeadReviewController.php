<?php
// /src/Controller/LeadReviewController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Lead;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * LeadReviewController
 * Admin-only moderation queue: every lead the extraction pipeline writes
 * lands as status = pending_review, and only becomes publicly visible
 * (status = active) once a human approves it here.
 */
class LeadReviewController
{
    public static function pending(int $perPage = 15): LengthAwarePaginator
    {
        return Lead::with(['location.parent', 'category', 'source'])
            ->where('status', 'pending_review')
            ->orderByDesc('scraped_at')
            ->paginate($perPage);
    }

    public static function approve(int $id): bool
    {
        $lead = Lead::where('status', 'pending_review')->find($id);
        if (!$lead) {
            return false;
        }

        $lead->status = 'active';
        return $lead->save();
    }

    public static function reject(int $id): bool
    {
        $lead = Lead::where('status', 'pending_review')->find($id);
        if (!$lead) {
            return false;
        }

        $lead->status = 'rejected';
        return $lead->save();
    }
}
