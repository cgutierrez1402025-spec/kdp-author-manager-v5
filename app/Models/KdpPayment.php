<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KdpPayment extends Model
{
    protected $guarded = [];

    protected $casts = ['payment_date' => 'date', 'accrued_royalty' => 'decimal:4', 'tax_withholding' => 'decimal:4', 'fx_rate' => 'decimal:8', 'payment_amount' => 'decimal:4', 'raw_data' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function latestImportBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'latest_import_batch_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(KdpPaymentAllocation::class);
    }
}
