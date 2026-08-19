<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiobookPronunciation extends Model
{
    protected $guarded = [];

    public function audiobookEdition(): BelongsTo
    {
        return $this->belongsTo(AudiobookEdition::class);
    }

    public function audiobookChapter(): BelongsTo
    {
        return $this->belongsTo(AudiobookChapter::class);
    }
}
