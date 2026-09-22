<?php
// /server/models/SwapListing.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwapListing extends Model
{
    protected $table = 'swp_listings';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'listing_type',
        'condition',
        'price',
        'trade_pref',
        'city',
        'video_name',
        'views',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'category_id' => 'integer',
        'price' => 'decimal:2',
        'views' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(SwapListingCategory::class, 'category_id');
    }

    public function pictures()
    {
        return $this->hasMany(SwapListingPic::class, 'listing_id')->orderBy('position');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    protected static function booted(): void
    {
        static::deleting(function (SwapListing $listing) {
            $listing->pictures()->get()->each(fn(SwapListingPic $pic) => $pic->delete());

            // Real Estate World's own equivalent (rew_quotations.video_name)
            // never does this and orphans the file on delete — fixed here.
            if ($listing->video_name) {
                $path = dirname(__DIR__, 2) . '/public/videos/swap-listings/' . basename($listing->video_name);
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        });
    }
}
