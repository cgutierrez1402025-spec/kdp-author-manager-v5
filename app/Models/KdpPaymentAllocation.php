<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KdpPaymentAllocation extends Model
{
    protected $guarded = [];

    protected $casts = ['allocated_amount' => 'decimal:4', 'confidence' => 'decimal:2'];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(KdpPayment::class, 'kdp_payment_id');
    }

    public function reportRow(): BelongsTo
    {
        return $this->belongsTo(KdpReportRow::class, 'kdp_report_row_id');
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
