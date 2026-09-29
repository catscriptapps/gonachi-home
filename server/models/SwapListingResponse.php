<?php
// /server/models/SwapListingResponse.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A prospective buyer/swapper's message to a listing's owner — the Swap
 * Marketplace equivalent of Real Estate World's QuotationResponse.
 */
class SwapListingResponse extends Model
{
    protected $table = 'swp_listing_responses';

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';

    protected $fillable = ['sender_id', 'listing_id', 'status', 'message'];

    protected $casts = [
        'sender_id' => 'integer',
        'listing_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(SwapListing::class, 'listing_id');
    }
}
