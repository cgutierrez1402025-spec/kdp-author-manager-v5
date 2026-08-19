<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KdpReportRow extends Model
{
    protected $guarded = [];

    protected $casts = [
        'report_period' => 'date',
        'transaction_date' => 'date',
        'payment_date' => 'date',
        'average_list_price' => 'decimal:4',
        'average_offer_price' => 'decimal:4',
        'average_delivery_cost' => 'decimal:4',
        'total_earnings' => 'decimal:4',
        'accrued_royalty' => 'decimal:4',
        'tax_withholding' => 'decimal:4',
        'fx_rate' => 'decimal:8',
        'payment_amount' => 'decimal:4',
        'raw_data' => 'array',
        'normalized_data' => 'array',
    ];

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
