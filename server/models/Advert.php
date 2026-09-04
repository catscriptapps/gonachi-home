<?php
// /server/models/Advert.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advert extends Model
{
    public const PACKAGE_FREE = 1;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_REJECTED = 'rejected';

    protected $table = 'rew_adverts';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'cta_id',
        'keywords',
        'landing_page_url',
        'selected_countries',
        'selected_user_types',
        'package_id',
        'status',
        'views',
        'expires_at',
        'video_name',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'cta_id' => 'integer',
        'selected_countries' => 'array',
        'selected_user_types' => 'array',
        'package_id' => 'integer',
        'views' => 'integer',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Cascading the FK handles the DB rows; this also unlinks each
        // picture's physical file (see AdvertPic::booted()).
        static::deleting(function (Advert $advert) {
            $advert->pictures()->get()->each(fn(AdvertPic $pic) => $pic->delete());

            if ($advert->video_name) {
                $path = __DIR__ . '/../../public/videos/adverts/' . basename($advert->video_name);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
        });
    }

    public function pictures(): HasMany
    {
        return $this->hasMany(AdvertPic::class, 'advert_id')->orderBy('pos_index');
    }

    public function cta(): BelongsTo
    {
        return $this->belongsTo(AdvertCta::class, 'cta_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(AdvertPackage::class, 'package_id', 'package_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
