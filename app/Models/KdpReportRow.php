<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KdpReportRow extends Model
{
    public const VALUE_COLUMNS = [
        'units_sold', 'units_refunded', 'net_units_sold', 'paid_units', 'free_units',
        'preorder_units', 'preorder_cancellations', 'net_preorder_units', 'kenp_read',
        'combined_units_or_kenp', 'average_list_price', 'average_offer_price',
        'average_delivery_cost', 'average_file_size_mb', 'total_earnings', 'income_amount',
        'kenp_rate', 'net_earnings', 'accrued_royalty', 'tax_withholding', 'fx_rate',
        'payment_amount',
    ];

    protected $guarded = [];

    protected $casts = [
        'report_period' => 'date',
        'snapshot_at' => 'datetime',
        'transaction_date' => 'date',
        'payment_date' => 'date',
        'average_list_price' => 'decimal:4',
        'average_offer_price' => 'decimal:4',
        'average_delivery_cost' => 'decimal:4',
        'total_earnings' => 'decimal:4',
        'average_file_size_mb' => 'decimal:4',
        'income_amount' => 'decimal:4',
        'net_earnings' => 'decimal:4',
        'kenp_rate' => 'decimal:8',
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

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(KdpCatalogItem::class, 'kdp_catalog_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWithNonZeroValues(Builder $query): Builder
    {
        return $query->whereRaw('('.self::nonZeroSql().')');
    }

    public function scopeOrderByDataPresence(Builder $query): Builder
    {
        return $query->orderByRaw('CASE WHEN '.self::nonZeroSql().' THEN 0 ELSE 1 END');
    }

    public static function nonZeroSql(?string $prefix = null): string
    {
        $prefix = $prefix ? rtrim($prefix, '.').'.' : '';

        return collect(self::VALUE_COLUMNS)
            ->map(fn (string $column): string => "COALESCE({$prefix}{$column}, 0) <> 0")
            ->implode(' OR ');
    }
}
