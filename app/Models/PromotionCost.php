<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_promotion_id',
        'cost_type',
        'description',
        'amount',
        'currency',
        'date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function bookPromotion(): BelongsTo
    {
        return $this->belongsTo(BookPromotion::class);
    }

    public function scopeByCostType($query, string $type)
    {
        return $query->where('cost_type', $type);
    }
}
