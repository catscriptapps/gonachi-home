<?php
// /src/Controller/JobRequestController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\JobRequest;
use App\Models\JobRequestPhoto;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * JobRequestController
 * Owns the Contractor Discovery "Job Request Engine": a client posts a
 * service need and contractors browse open requests by category/location.
 * No moderation gate — unlike landlord reports, nothing here names or
 * accuses a specific person, so requests publish immediately.
 */
class JobRequestController
{
    private const REQUIRED_FIELDS = ['service_category', 'location', 'description', 'contact_phone'];

    private const VALID_CATEGORIES = [
        'plumbing', 'electrical', 'painting', 'building_construction',
        'interior_design', 'renovation', 'solar_installation', 'other',
    ];

    /**
     * Only URLs under this path are trusted when attaching photos — guards
     * against a crafted payload smuggling in an external URL. See
     * job-request-photo-upload.php, the only writer of this path (mirrors
     * the same guard in LandlordDirectoryController).
     */
    private const ALLOWED_UPLOAD_PATH = 'images/uploads/job-requests/';

    /**
     * @param array $input Decoded JSON body: service_category, location, budget,
     *                      description, timeline, contact_phone, photo_urls[].
     * @return array{success: bool, errors: string[]}
     */
    public static function submit(array $input, int $userId): array
    {
        $errors = [];
        foreach (self::REQUIRED_FIELDS as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                $errors[] = "The {$field} field is required.";
            }
        }

        if (!in_array($input['service_category'] ?? '', self::VALID_CATEGORIES, true)) {
            $errors[] = 'Please select a valid service category.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $budget = trim((string) ($input['budget'] ?? ''));

        $jobRequest = JobRequest::create([
            'user_id' => $userId,
            'service_category' => (string) $input['service_category'],
            'location' => trim((string) $input['location']),
            'budget' => $budget !== '' ? (float) $budget : null,
            'description' => trim((string) $input['description']),
            'timeline' => trim((string) ($input['timeline'] ?? '')) ?: null,
            'contact_phone' => trim((string) $input['contact_phone']),
            'status' => 'open',
        ]);

        self::attachPhotos($jobRequest, $input['photo_urls'] ?? []);

        return ['success' => true, 'errors' => []];
    }

    /**
     * Real, open job requests — newest first, optional category/location filters.
     */
    public static function openRequests(?string $category, ?string $location, int $perPage = 10): LengthAwarePaginator
    {
        $query = JobRequest::open()->with(['photos', 'user'])->orderByDesc('created_at');

        if ($category) {
            $query->where('service_category', $category);
        }

        if ($location) {
            $query->where('location', 'like', "%{$location}%");
        }

        return $query->paginate($perPage);
    }

    /**
     * Marks a request closed — only if the caller owns it.
     */
    public static function close(int $id, int $userId): bool
    {
        $jobRequest = JobRequest::where('id', $id)->where('user_id', $userId)->first();
        if (!$jobRequest) {
            return false;
        }

        $jobRequest->status = 'closed';
        return $jobRequest->save();
    }

    /**
     * Live counter for the page header.
     */
    public static function totalOpenCount(): int
    {
        return JobRequest::open()->count();
    }

    /**
     * @param string[] $urls Already-uploaded URLs, e.g. "/images/uploads/job-requests/xyz.jpg"
     *                       locally or "/gonachi-home/images/uploads/job-requests/xyz.jpg" on a
     *                       production deploy under a subdirectory (getAssetBase() prefixes
     *                       whichever applies — see job-request-photo-upload.php).
     */
    private static function attachPhotos(JobRequest $jobRequest, array $urls): void
    {
        $assetBase = getAssetBase();

        foreach ($urls as $url) {
            $url = trim((string) $url);

            if ($url === '' || !str_starts_with($url, $assetBase)) {
                continue;
            }

            // Strip the environment's base path back off so file_path is
            // always stored relative — read paths prepend $assetBase fresh
            // each time, so a baked-in base path here would break the moment
            // the environment's subdirectory ever changed.
            $relative = substr($url, strlen($assetBase));

            if (!str_starts_with($relative, self::ALLOWED_UPLOAD_PATH)) {
                continue;
            }

            JobRequestPhoto::create([
                'job_request_id' => $jobRequest->id,
                'file_path' => $relative,
            ]);
        }
    }
}
