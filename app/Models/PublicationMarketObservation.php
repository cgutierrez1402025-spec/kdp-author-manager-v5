<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationMarketObservation extends Model
{
    protected $fillable = ['publication_id', 'marketplace_id', 'observed_at', 'average_rating', 'rating_count', 'review_count', 'overall_rank', 'category_name', 'category_rank', 'extra_metrics', 'source'];

    protected $casts = ['observed_at' => 'date', 'average_rating' => 'decimal:2', 'extra_metrics' => 'array'];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }
}
