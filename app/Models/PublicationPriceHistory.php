<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class PublicationPriceHistory extends Model
{
    protected $fillable = ['publication_id', 'marketplace_id', 'price', 'currency', 'starts_at', 'ends_at', 'change_reason', 'notes'];

    protected $casts = ['price' => 'decimal:2', 'starts_at' => 'date', 'ends_at' => 'date'];

    protected static function booted(): void
    {
        static::saving(function (self $price): void {
            if ($price->ends_at && $price->ends_at->lt($price->starts_at)) {
                throw ValidationException::withMessages(['ends_at' => 'La fecha final no puede ser anterior a la inicial.']);
            }

            $newEnd = $price->ends_at?->toDateString() ?? '9999-12-31';
            $overlaps = self::query()->where('publication_id', $price->publication_id)->where('marketplace_id', $price->marketplace_id)
                ->when($price->exists, fn ($query) => $query->whereKeyNot($price->getKey()))
                ->whereDate('starts_at', '<=', $newEnd)
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', $price->starts_at->toDateString()))
                ->exists();

            if ($overlaps) {
                throw ValidationException::withMessages(['starts_at' => 'El periodo se solapa con otro precio de la misma publicación y mercado.']);
            }
        });
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }
}
