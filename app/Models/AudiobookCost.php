<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiobookCost extends Model
{
    protected $guarded = [];

    protected $casts = ['incurred_at' => 'date'];

    public function audiobookEdition(): BelongsTo
    {
        return $this->belongsTo(AudiobookEdition::class);
    }
}
