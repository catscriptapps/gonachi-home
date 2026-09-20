<?php
// /src/Controller/LandlordReportReviewController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\LandlordRecord;
use App\Models\LandlordReport;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * LandlordReportReviewController
 * Admin-only moderation queue: every submitted report lands as
 * status = pending_review, and only counts toward a property's public
 * confidence score / search results once a human approves it here.
 * Exact mirror of LeadReviewController for the real-estate-leads project.
 */
class LandlordReportReviewController
{
    public static function pending(int $perPage = 15): LengthAwarePaginator
    {
        return LandlordReport::with(['property', 'landlord', 'user', 'photos'])
            ->where('status', 'pending_review')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public static function approve(int $id): bool
    {
        $report = LandlordReport::where('status', 'pending_review')->find($id);
        if (!$report) {
            return false;
        }

        $report->status = 'published';
        $saved = $report->save();

        // Backfill the landlord's phone — only once a report clears review,
        // matching how everything else here (confidence score, published
        // report count) only ever counts approved reports. Never overwrites
        // an already-known number, so one wrong/malicious submission can't
        // clobber a previously-corroborated contact — see
        // LandlordCreditService::unlockContact(), the credit system this feeds.
        if ($saved && $report->landlord_phone) {
            $landlord = LandlordRecord::find($report->landlord_id);
            if ($landlord && !$landlord->phone) {
                $landlord->phone = $report->landlord_phone;
                $landlord->save();
            }
        }

        return $saved;
    }

    public static function reject(int $id): bool
    {
        $report = LandlordReport::where('status', 'pending_review')->find($id);
        if (!$report) {
            return false;
        }

        $report->status = 'rejected';
        return $report->save();
    }
}
