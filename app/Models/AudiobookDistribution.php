<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiobookDistribution extends Model
{
    protected $guarded = [];

    protected $casts = ['exclusive' => 'boolean', 'territories' => 'array', 'published_at' => 'datetime'];

    public function audiobookEdition(): BelongsTo
    {
        return $this->belongsTo(AudiobookEdition::class);
    }
}
