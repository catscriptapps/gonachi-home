<?php
// /server/models/Review.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'ltv_reviews';

    protected $fillable = [
        'tenancy_id',
        'reviewer_user_id',
        'reviewee_type',
        'reviewee_landlord_id',
        'reviewee_tenant_id',
        'comment',
        'release_status',
        'released_at',
        'moderation_status',
        'country_id',
    ];

    protected $casts = [
        'tenancy_id' => 'integer',
        'reviewer_user_id' => 'integer',
        'reviewee_landlord_id' => 'integer',
        'reviewee_tenant_id' => 'integer',
        'country_id' => 'integer',
        'released_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class, 'tenancy_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    public function revieweeLandlord()
    {
        return $this->belongsTo(LandlordRecord::class, 'reviewee_landlord_id');
    }

    public function revieweeTenant()
    {
        return $this->belongsTo(TenantRecord::class, 'reviewee_tenant_id');
    }

    public function criterionScores()
    {
        return $this->hasMany(ReviewCriterionScore::class, 'review_id');
    }

    public function tags()
    {
        return $this->hasMany(ReviewTag::class, 'review_id');
    }

    public function reports()
    {
        return $this->hasMany(ReviewReport::class, 'review_id');
    }

    public function response()
    {
        return $this->hasOne(ReviewResponse::class, 'review_id');
    }

    public function scopeVisible($query)
    {
        return $query->where('release_status', 'released')->where('moderation_status', 'published');
    }
}
