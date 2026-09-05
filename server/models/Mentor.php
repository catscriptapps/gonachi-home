<?php
// /server/models/Mentor.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mentor extends Model
{
    protected $table = 'rew_mentors';

    public $timestamps = true;

    protected $fillable = [
        'orig_user_id',
        'country_id',
        'region_id',
        'city',
        'target_stakeholder_type_id',
        'headline',
        'bio',
        'skills',
        'years_experience',
        'youtube_url',
        'website_url',
        'is_active',
    ];

    protected $casts = [
        'id' => 'integer',
        'orig_user_id' => 'integer',
        'country_id' => 'integer',
        'region_id' => 'integer',
        'target_stakeholder_type_id' => 'integer',
        'years_experience' => 'integer',
        'skills' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'orig_user_id', 'id');
    }

    public function stakeholderType(): BelongsTo
    {
        return $this->belongsTo(StakeholderType::class, 'target_stakeholder_type_id', 'id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(MentorRequest::class, 'mentor_id', 'id');
    }
}
