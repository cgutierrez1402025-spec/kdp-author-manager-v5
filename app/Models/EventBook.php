<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'work_id',
        'edition_id',
        'work_language_id',
        'copies_brought',
        'copies_sold',
        'copies_gifted',
        'copies_returned',
        'unit_sale_price',
        'income_amount',
        'notes',
    ];

    protected $casts = [
        'unit_sale_price' => 'decimal:2',
        'income_amount' => 'decimal:2',
    ];

    public function bookEvent(): BelongsTo
    {
        return $this->belongsTo(BookEvent::class, 'event_id');
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function workLanguage(): BelongsTo
    {
        return $this->belongsTo(WorkLanguage::class);
    }

    public function getIncomeCalculatedAttribute(): float
    {
        return (float) ($this->copies_sold * $this->unit_sale_price);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->income_amount = $model->income_calculated;
        });

        static::updating(function ($model) {
            if ($model->wasChanged(['copies_sold', 'unit_sale_price'])) {
                $model->income_amount = $model->income_calculated;
            }
        });
    }
}
