<?php
// /server/models/LandlordReportPhoto.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordReportPhoto extends Model
{
    protected $table = 'ltv_report_photos';

    protected $fillable = [
        'report_id',
        'kind',
        'file_path',
    ];

    protected $casts = [
        'report_id' => 'integer',
    ];

    public function report()
    {
        return $this->belongsTo(LandlordReport::class, 'report_id');
    }
}
