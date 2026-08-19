<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiobookProduction extends Model
{
    protected $guarded = [];

    protected $casts = ['offer_date' => 'date', 'accepted_at' => 'date', 'due_date' => 'date', 'approved_at' => 'date'];

    public function audiobookEdition(): BelongsTo
    {
        return $this->belongsTo(AudiobookEdition::class);
    }

    public function narrator(): BelongsTo
    {
        return $this->belongsTo(Narrator::class);
    }
}
