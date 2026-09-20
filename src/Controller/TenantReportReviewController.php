<?php
// /src/Controller/TenantReportReviewController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\TenantRecord;
use App\Models\TenantReport;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * TenantReportReviewController
 * Admin-only moderation queue for reports landlords file against tenants —
 * exact mirror of LandlordReportReviewController for the opposite
 * direction of landlord_and_tenant_validation.pdf's Product Vision.
 */
class TenantReportReviewController
{
    public static function pending(int $perPage = 15): LengthAwarePaginator
    {
        return TenantReport::with(['tenant', 'user'])
            ->where('status', 'pending_review')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public static function approve(int $id): bool
    {
        $report = TenantReport::where('status', 'pending_review')->find($id);
        if (!$report) {
            return false;
        }

        $report->status = 'published';
        $saved = $report->save();

        // Backfill the tenant's reference phone — only once a report clears
        // review, and never overwrites an already-known one. Mirrors
        // LandlordReportReviewController::approve()'s landlord phone
        // backfill exactly.
        if ($saved && $report->reference_phone) {
            $tenant = TenantRecord::find($report->tenant_id);
            if ($tenant && !$tenant->reference_phone) {
                $tenant->reference_phone = $report->reference_phone;
                $tenant->save();
            }
        }

        return $saved;
    }

    public static function reject(int $id): bool
    {
        $report = TenantReport::where('status', 'pending_review')->find($id);
        if (!$report) {
            return false;
        }

        $report->status = 'rejected';
        return $report->save();
    }
}
