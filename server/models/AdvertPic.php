<?php
// /server/models/AdvertPic.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertPic extends Model
{
    protected $table = 'rew_advert_pics';

    protected $fillable = [
        'advert_id',
        'pic_name',
        'pic_caption',
        'pos_index',
    ];

    protected $casts = [
        'advert_id' => 'integer',
        'pos_index' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (AdvertPic $pic) {
            if (empty($pic->pic_name)) {
                return;
            }

            $path = __DIR__ . '/../../public/images/uploads/adverts/' . basename($pic->pic_name);
            if (file_exists($path)) {
                @unlink($path);
            }
        });
    }

    public function advert(): BelongsTo
    {
        return $this->belongsTo(Advert::class, 'advert_id');
    }
}
