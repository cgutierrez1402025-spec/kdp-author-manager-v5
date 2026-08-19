<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiobookRoyalty extends Model
{
    protected $guarded = [];

    protected $casts = ['period_start' => 'date', 'period_end' => 'date'];

    public function audiobookEdition(): BelongsTo
    {
        return $this->belongsTo(AudiobookEdition::class);
    }
}
