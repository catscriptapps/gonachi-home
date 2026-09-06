<?php
// /server/models/Listing.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    protected $table = 'rew_listings';
    protected $primaryKey = 'listing_id';

    public $incrementing = true;

    public const STATUS_ACTIVE = 1;
    public const STATUS_ARCHIVED = 2;

    protected $fillable = [
        'orig_user_id',
        'listing_title',
        'city',
        'category_id',
        'category_type_id',
        'unit_type_id',
        'house_type_id',
        'bedroom_id',
        'bathroom_id',
        'listing_description',
        'address',
        'country_id',
        'region_id',
        'agreement_type_id',
        'price',
        'property_size',
        'move_in_date',
        'is_ac',
        'is_furnished',
        'parking',
        'pets_allowed',
        'amenities',
        'youtube_url',
        'contact_phone',
        'status_id',
        'views',
    ];

    protected $casts = [
        'listing_id' => 'integer',
        'orig_user_id' => 'integer',
        'category_id' => 'integer',
        'category_type_id' => 'integer',
        'unit_type_id' => 'integer',
        'house_type_id' => 'integer',
        'bedroom_id' => 'integer',
        'bathroom_id' => 'integer',
        'country_id' => 'integer',
        'region_id' => 'integer',
        'agreement_type_id' => 'integer',
        'is_ac' => 'integer',
        'is_furnished' => 'integer',
        'parking' => 'integer',
        'pets_allowed' => 'integer',
        'status_id' => 'integer',
        'views' => 'integer',
        'amenities' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getAmenityModels()
    {
        if (empty($this->amenities)) {
            return collect();
        }

        return Amenity::whereIn('amenity_id', $this->amenities)->get();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'orig_user_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ListingCategory::class, 'category_id', 'category_id');
    }

    public function categoryType(): BelongsTo
    {
        return $this->belongsTo(ListingCategoryType::class, 'category_type_id', 'category_type_id');
    }

    public function unitType(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_type_id', 'unit_type_id');
    }

    public function houseType(): BelongsTo
    {
        return $this->belongsTo(HouseType::class, 'house_type_id', 'house_type_id');
    }

    public function bedroom(): BelongsTo
    {
        return $this->belongsTo(Bedroom::class, 'bedroom_id', 'bedroom_id');
    }

    public function bathroom(): BelongsTo
    {
        return $this->belongsTo(Bathroom::class, 'bathroom_id', 'bathroom_id');
    }

    public function agreementType(): BelongsTo
    {
        return $this->belongsTo(AgreementType::class, 'agreement_type_id', 'agreement_type_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function pictures(): HasMany
    {
        return $this->hasMany(ListingPic::class, 'listing_id', 'listing_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ListingResponse::class, 'listing_id', 'listing_id');
    }

    protected static function booted()
    {
        static::deleting(function (Listing $listing) {
            $listing->pictures()->get()->each(fn (ListingPic $pic) => $pic->delete());
        });
    }
}
