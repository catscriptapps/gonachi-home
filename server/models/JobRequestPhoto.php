<?php
// /server/models/JobRequestPhoto.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobRequestPhoto extends Model
{
    protected $table = 'cde_job_request_photos';

    protected $fillable = [
        'job_request_id',
        'file_path',
    ];

    protected $casts = [
        'job_request_id' => 'integer',
    ];

    public function jobRequest()
    {
        return $this->belongsTo(JobRequest::class, 'job_request_id');
    }
}
