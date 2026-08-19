<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
        'marketplace_id',
        'kdp_select_period_id',
        'promotion_type',
        'promotion_name',
        'start_date',
        'end_date',
        'normal_price',
        'promotional_price',
        'status',
        'objective',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'normal_price' => 'decimal:2',
        'promotional_price' => 'decimal:2',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function kdpSelectPeriod(): BelongsTo
    {
        return $this->belongsTo(KdpSelectPeriod::class);
    }

    public function dailyResults(): HasMany
    {
        return $this->hasMany(PromotionDailyResult::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(PromotionCost::class);
    }

    public function getTotalSalesAttribute(): int
    {
        return $this->dailyResults()->sum('paid_units') +
               $this->dailyResults()->sum('free_units_promo') +
               $this->dailyResults()->sum('free_units_price_match');
    }

    public function getTotalRevenueAttribute(): float
    {
        return (float) $this->dailyResults()->sum('gross_royalties');
    }

    public function getTotalCostsAttribute(): float
    {
        return (float) $this->costs()->sum('amount');
    }

    public function getTotalFreeDaysUsed(): int
    {
        return $this->dailyResults()->sum('free_units_promo');
    }

    public function calculateROI(): float
    {
        $costs = $this->getTotalCostsAttribute();
        $royalties = $this->getTotalRevenueAttribute();

        if ($costs === 0.0) {
            return 0.0;
        }

        return round((($royalties - $costs) / $costs) * 100, 2);
    }

    public function getRemainingFreeDays(): int
    {
        if (! $this->kdpSelectPeriod) {
            return 0;
        }

        return $this->kdpSelectPeriod->getRemainingFreeDays();
    }

    public function validateDates(?int $excludeId = null): array
    {
        $errors = [];

        if ($this->start_date > $this->end_date) {
            $errors[] = 'La fecha de inicio no puede ser posterior a la fecha de fin';
        }

        $overlapping = static::where('publication_id', $this->publication_id)
            ->where('id', '!=', $excludeId ?? $this->id ?? 0)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                    ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                    ->orWhere(function ($q) {
                        $q->where('start_date', '<', $this->start_date)
                            ->where('end_date', '>', $this->end_date);
                    });
            })
            ->exists();

        if ($overlapping) {
            $errors[] = 'Existe otra promoción activa que se solapa con estas fechas';
        }

        return $errors;
    }

    public function scopeActive($query, ?string $date = null)
    {
        $date = $date ?? now()->toDateString();

        return $query->where('status', 'active')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);
    }

    public function scopeUpcoming($query, ?string $date = null)
    {
        $date = $date ?? now()->toDateString();

        return $query->where('status', 'active')
            ->where('start_date', '>', $date);
    }

    public function scopePast($query, ?string $date = null)
    {
        $date = $date ?? now()->toDateString();

        return $query->where('end_date', '<', $date);
    }
}
