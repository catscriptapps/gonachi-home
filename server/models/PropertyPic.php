<?php
// /server/models/PropertyPic.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyPic extends Model
{
    protected $table = 'properties_pics';
    protected $primaryKey = 'entry_id';

    public $incrementing = true;

    protected $fillable = [
        'property_id',
        'pic_name',
        'pic_caption',
        'pos_index',
    ];

    protected $casts = [
        'entry_id'    => 'integer',
        'property_id' => 'integer',
        'pos_index'   => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }

    public function isOwnedBy(int $landlordId): bool
    {
        return $this->property && (int)$this->property->landlord_id === $landlordId;
    }

    protected static function booted()
    {
        static::deleting(function ($pic) {
            $basePath = dirname(__DIR__, 2);
            $filePath = $basePath . '/public/images/uploads/properties/' . $pic->pic_name;

            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        });
    }
}
