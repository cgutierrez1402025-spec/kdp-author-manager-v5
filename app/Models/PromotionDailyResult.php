<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionDailyResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_promotion_id',
        'date',
        'paid_units',
        'free_units_promo',
        'free_units_price_match',
        'kenp_pages_read',
        'gross_royalties',
        'net_royalties',
        'currency',
        'ranking_position',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'gross_royalties' => 'decimal:2',
        'net_royalties' => 'decimal:2',
    ];

    public function bookPromotion(): BelongsTo
    {
        return $this->belongsTo(BookPromotion::class);
    }

    public function getTotalUnitsAttribute(): int
    {
        return $this->paid_units + $this->free_units_promo + $this->free_units_price_match;
    }

    public function getTotalFreeUnitsAttribute(): int
    {
        return $this->free_units_promo + $this->free_units_price_match;
    }
}
