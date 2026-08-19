<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KdpRoyaltyEstimate extends Model
{
    protected $guarded = [];

    protected $casts = [
        'period' => 'date', 'snapshot_at' => 'datetime', 'estimated_amount' => 'decimal:4',
        'kenp_rate' => 'decimal:8', 'filters' => 'array',
    ];

    public function reportRow(): BelongsTo
    {
        return $this->belongsTo(KdpReportRow::class, 'kdp_report_row_id');
    }
}
