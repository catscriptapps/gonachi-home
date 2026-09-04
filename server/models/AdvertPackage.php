<?php
// /server/models/AdvertPackage.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A cosmetic tier label shown on an advert (Free/Standard/Pro/Business/
 * Ultimate) — matches the legacy platform exactly: no price field, no
 * payment/duration enforcement anywhere. package_description is a
 * free-text duration label ("3 Days", "1 Week", ...) that nothing actually
 * reads to compute expiry.
 */
class AdvertPackage extends Model
{
    protected $table = 'rew_advert_packages';
    protected $primaryKey = 'package_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'package_id',
        'package_name',
        'package_description',
        'package_icon',
        'package_order',
    ];

    protected $casts = [
        'package_id' => 'integer',
        'package_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
