<?php
// /src/Controller/QuotationsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Quotation;
use App\Models\User;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;

/**
 * Owns the Real Estate World Quotations module — ported from the legacy
 * gonachi/ platform (Src\Controller\QuotationsController). Same shape as
 * legacy: a quotation is Active or Archived by the owner's own toggle (no
 * admin moderation queue), the public browse feed only ever shows Active
 * ones, and "My Quotations" shows the owner's own regardless of status.
 *
 * Legacy's bid/accept/decline handshake runs entirely through its
 * Notification system, which gonachi-home doesn't have yet. Here the same
 * QuotationResponse data and accept/decline logic (QuotationResponsesController)
 * is surfaced directly inside the quotation's own view modal as a
 * "Responses" list instead of a notification bell.
 */
class QuotationsController
{
    use RecentActivityLogger;

    private const EAGER = [
        'owner.country',
        'owner.region',
        'country',
        'region',
        'contractorType',
        'skilledTrade',
        'unitType',
        'houseType',
        'quotationType',
        'destination',
        'pictures',
    ];

    public static function incrementView(string $encodedId, int $viewerId): ?int
    {
        $id = self::decodeId($encodedId);
        $quote = $id ? Quotation::find($id) : null;

        if (!$quote) {
            return null;
        }

        if ((int) $quote->orig_user_id !== $viewerId) {
            $quote->increment('views');
        }

        return $quote->fresh()->views;
    }

    /**
     * Public "Browse Quotations" feed — Active only, optional search.
     *
     * @return array{html: string, count: int}
     */
    public static function browse(?string $search, int $viewerId): array
    {
        $query = Quotation::with(self::EAGER)->where('status_id', Quotation::STATUS_ACTIVE);
        self::applySearch($query, $search);

        $quotes = $query->orderByDesc('created_at')->get();

        return [
            'html' => self::renderCards($quotes, $viewerId),
            'count' => $quotes->count(),
        ];
    }

    /**
     * "My Quotations": the owner's own requests, every status.
     *
     * @return array{html: string, count: int}
     */
    public static function mine(?string $search, int $userId): array
    {
        $query = Quotation::with(self::EAGER)->where('orig_user_id', $userId);
        self::applySearch($query, $search);

        $quotes = $query->orderByDesc('created_at')->get();

        return [
            'html' => self::renderCards($quotes, $userId),
            'count' => $quotes->count(),
        ];
    }

    public static function totalActiveCount(): int
    {
        return Quotation::where('status_id', Quotation::STATUS_ACTIVE)->count();
    }

    /**
     * Create (no encoded_id) or update (owner-only) a quotation request.
     *
     * @return array{success: bool, errors: string[], quotation: ?Quotation}
     */
    public static function save(array $input, int $userId): array
    {
        $title = trim((string) ($input['quotation_title'] ?? ''));
        if ($title === '') {
            return ['success' => false, 'errors' => ['A title is required.'], 'quotation' => null];
        }

        $encodedId = (string) ($input['encoded_id'] ?? '');
        $id = $encodedId !== '' ? self::decodeId($encodedId) : null;

        $unitTypeId = (int) ($input['unit_type_id'] ?? 0) ?: null;

        $data = [
            'quotation_title' => $title,
            'description_of_work_to_be_done' => trim((string) ($input['description_of_work_to_be_done'] ?? '')),
            'contractor_type_id' => (int) ($input['contractor_type_id'] ?? 0) ?: null,
            'skilled_trade_id' => (int) ($input['skilled_trade_id'] ?? 0) ?: null,
            'unit_type_id' => $unitTypeId,
            'house_type_id' => $unitTypeId === 5 ? ((int) ($input['house_type_id'] ?? 0) ?: null) : null,
            'quotation_type_id' => (int) ($input['quotation_type_id'] ?? 0) ?: null,
            'quotation_dest_id' => (int) ($input['quotation_dest_id'] ?? 0) ?: null,
            'country_id' => (int) ($input['country_id'] ?? 0) ?: null,
            'region_id' => (int) ($input['region_id'] ?? 0) ?: null,
            'city' => trim((string) ($input['city'] ?? '')) ?: null,
            'start_date' => trim((string) ($input['start_date'] ?? '')) ?: null,
            'finish_date' => trim((string) ($input['finish_date'] ?? '')) ?: null,
            'start_time' => trim((string) ($input['start_time'] ?? '')) ?: null,
            'finish_time' => trim((string) ($input['finish_time'] ?? '')) ?: null,
            'quotation_budget' => trim((string) ($input['quotation_budget'] ?? '')) ?: null,
            'youtube_url' => trim((string) ($input['youtube_url'] ?? '')) ?: null,
            'contact_phone' => trim((string) ($input['contact_phone'] ?? '')) ?: null,
        ];

        if ($id) {
            $quote = Quotation::where('quotation_id', $id)->where('orig_user_id', $userId)->first();
            if (!$quote) {
                return ['success' => false, 'errors' => ['You can only edit your own quotations.'], 'quotation' => null];
            }
            $quote->update($data);
        } else {
            $data['orig_user_id'] = $userId;
            $data['status_id'] = Quotation::STATUS_ACTIVE;
            $data['views'] = 0;
            $quote = Quotation::create($data);
        }

        $quote->load(self::EAGER);
        $actionLabel = $id ? 'Updated quotation' : 'Posted new quotation';
        self::logActivity("{$actionLabel}: {$quote->quotation_title}", 'Quotation', $quote->quotation_id, $userId);

        return ['success' => true, 'errors' => [], 'quotation' => $quote];
    }

    /**
     * Owner-only. Cascades to delete pictures (DB rows + files, via model events).
     */
    public static function delete(string $encodedId, int $userId): array
    {
        $id = self::decodeId($encodedId);
        $quote = $id ? Quotation::where('quotation_id', $id)->where('orig_user_id', $userId)->first() : null;

        if (!$quote) {
            return ['success' => false, 'message' => 'Quotation not found, or not yours to delete.'];
        }

        $title = $quote->quotation_title;
        $quote->delete();
        self::logActivity("Deleted quotation: {$title}", 'Quotation', $id, $userId);

        return ['success' => true];
    }

    /**
     * Owner-only toggle between Active and Archived.
     */
    public static function setStatus(string $encodedId, int $statusId, int $userId): array
    {
        if (!in_array($statusId, [Quotation::STATUS_ACTIVE, Quotation::STATUS_ARCHIVED], true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }

        $id = self::decodeId($encodedId);
        $quote = $id ? Quotation::with(self::EAGER)->where('quotation_id', $id)->where('orig_user_id', $userId)->first() : null;

        if (!$quote) {
            return ['success' => false, 'message' => 'Quotation not found, or not yours to manage.'];
        }

        $quote->status_id = $statusId;
        $quote->save();

        $verb = $statusId === Quotation::STATUS_ACTIVE ? 'Reactivated' : 'Deactivated';
        self::logActivity("{$verb} quotation: {$quote->quotation_title}", 'Quotation', $quote->quotation_id, $userId);

        return ['success' => true, 'quotation' => $quote];
    }

    public static function renderCard(Quotation $quote, int $viewerId): string
    {
        $data = self::buildItemArray($quote, $viewerId);
        $assetBase = getAssetBase();

        ob_start();
        include __DIR__ . '/../../resources/views/components/quotations/data-card.php';
        return ob_get_clean() ?: '';
    }

    private static function renderCards($quotes, int $viewerId): string
    {
        $html = '';
        foreach ($quotes as $quote) {
            $html .= self::renderCard($quote, $viewerId);
        }
        return $html;
    }

    private static function applySearch($query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $term = '%' . trim($search) . '%';
        $query->where(function ($q) use ($term) {
            $q->where('quotation_title', 'like', $term)
                ->orWhere('city', 'like', $term)
                ->orWhere('description_of_work_to_be_done', 'like', $term)
                ->orWhereHas('contractorType', fn ($r) => $r->where('contractor_type', 'like', $term))
                ->orWhereHas('skilledTrade', fn ($r) => $r->where('skilled_trade', 'like', $term))
                ->orWhereHas('country', fn ($r) => $r->where('country', 'like', $term))
                ->orWhereHas('region', fn ($r) => $r->where('region', 'like', $term));
        });
    }

    /**
     * @return array<string, mixed> Everything data-card.php / the view modal's
     *                              data-* payload need.
     */
    private static function buildItemArray(Quotation $quote, int $viewerId): array
    {
        $owner = $quote->owner;
        $firstPic = $quote->pictures->sortBy('pos_index')->first();

        return [
            'encoded_id' => IdEncoder::encode((int) $quote->quotation_id),
            'title' => $quote->quotation_title,
            'description' => $quote->description_of_work_to_be_done,
            'city' => $quote->city,
            'country_id' => $quote->country_id,
            'country_name' => $quote->country->country ?? '',
            'region_id' => $quote->region_id,
            'region_name' => $quote->region->region ?? '',
            'contractor_type_id' => $quote->contractor_type_id,
            'contractor_type_name' => $quote->contractorType->contractor_type ?? 'Any Contractor',
            'skilled_trade_id' => $quote->skilled_trade_id,
            'skilled_trade_name' => $quote->skilledTrade->skilled_trade ?? 'General',
            'unit_type_id' => $quote->unit_type_id,
            'unit_type_name' => $quote->unitType->unit_type ?? '',
            'house_type_id' => $quote->house_type_id,
            'house_type_name' => $quote->houseType->house_type ?? '',
            'quotation_type_id' => $quote->quotation_type_id,
            'quotation_type_name' => $quote->quotationType->quotation_type ?? '',
            'quotation_dest_id' => $quote->quotation_dest_id,
            'quotation_dest_name' => $quote->destination->quotation_dest ?? '',
            'budget' => $quote->quotation_budget,
            'start_date' => $quote->start_date,
            'finish_date' => $quote->finish_date,
            'start_time' => $quote->start_time,
            'finish_time' => $quote->finish_time,
            'contact_phone' => $quote->contact_phone,
            'youtube_url' => $quote->youtube_url,
            'status_id' => $quote->status_id,
            'views' => $quote->views,
            'created_at' => $quote->created_at,
            'updated_at' => $quote->updated_at,
            'thumbnail' => $firstPic->pic_name ?? null,
            'owner_id' => (int) $quote->orig_user_id,
            'owner_name' => $owner->full_name ?? 'User',
            'owner_avatar' => $owner->avatar_url ?? null,
            'owner_initial' => strtoupper(substr($owner->full_name ?? 'U', 0, 1)),
            'owner_location' => $owner ? trim(($owner->city ?: 'Remote') . ', ' . ($owner->country->country ?? '')) : 'Unknown',
            'is_card_owner' => (int) $quote->orig_user_id === $viewerId,
        ];
    }

    private static function decodeId(string $raw): ?int
    {
        if ($raw === '') {
            return null;
        }

        if (ctype_digit($raw)) {
            return (int) $raw;
        }

        return IdEncoder::decode($raw);
    }
}
