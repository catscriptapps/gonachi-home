<?php
// /server/models/ListingPic.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingPic extends Model
{
    protected $table = 'rew_listing_pics';
    protected $primaryKey = 'entry_id';

    public $incrementing = true;

    protected $fillable = ['listing_id', 'pic_name', 'pos_index'];

    protected $casts = [
        'entry_id' => 'integer',
        'listing_id' => 'integer',
        'pos_index' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'listing_id');
    }

    public function isOwnedBy(int $userId): bool
    {
        return $this->listing && (int) $this->listing->orig_user_id === $userId;
    }

    protected static function booted()
    {
        static::deleting(function (ListingPic $pic) {
            $path = dirname(__DIR__, 2) . '/public/images/uploads/listings/' . $pic->pic_name;
            if (file_exists($path)) {
                @unlink($path);
            }
        });
    }
}
