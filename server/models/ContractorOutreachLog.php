<?php
// /server/models/ContractorOutreachLog.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractorOutreachLog extends Model
{
    protected $table = 'cde_contractor_outreach_log';

    protected $fillable = [
        'contractor_id',
        'sent_by_user_id',
        'channel',
        'recipient',
        'message',
        'status',
        'error_message',
    ];

    protected $casts = [
        'contractor_id' => 'integer',
        'sent_by_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id');
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }
}
