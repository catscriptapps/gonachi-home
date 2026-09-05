<?php
// /server/models/QuotationPic.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationPic extends Model
{
    protected $table = 'rew_quotation_pics';
    protected $primaryKey = 'entry_id';

    public $incrementing = true;

    protected $fillable = ['quotation_id', 'pic_name', 'pos_index'];

    protected $casts = [
        'entry_id' => 'integer',
        'quotation_id' => 'integer',
        'pos_index' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id', 'quotation_id');
    }

    public function isOwnedBy(int $userId): bool
    {
        return $this->quotation && (int) $this->quotation->orig_user_id === $userId;
    }

    protected static function booted()
    {
        static::deleting(function (QuotationPic $pic) {
            $path = dirname(__DIR__, 2) . '/public/images/uploads/quotations/' . $pic->pic_name;
            if (file_exists($path)) {
                @unlink($path);
            }
        });
    }
}
