<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KdpSelectPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
        'start_date',
        'end_date',
        'auto_renewal',
        'free_promo_days_allowed',
        'free_promo_days_used',
        'free_promo_days_remaining',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renewal' => 'boolean',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function bookPromotions(): HasMany
    {
        return $this->hasMany(BookPromotion::class);
    }

    public function getRemainingFreeDays(): int
    {
        return $this->free_promo_days_remaining ?? 0;
    }

    public function decrementFreeDays(int $days = 1): void
    {
        $this->increment('free_promo_days_used', $days);
        $this->decrement('free_promo_days_remaining', $days);
    }

    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->start_date->isFuture() === false && $this->end_date->isFuture();
    }
}
