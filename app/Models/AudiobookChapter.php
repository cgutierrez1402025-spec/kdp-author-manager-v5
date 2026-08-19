<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AudiobookChapter extends Model
{
    protected $guarded = [];

    public function audiobookEdition(): BelongsTo
    {
        return $this->belongsTo(AudiobookEdition::class);
    }

    public function narrator(): BelongsTo
    {
        return $this->belongsTo(Narrator::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(AudioAsset::class);
    }
}
