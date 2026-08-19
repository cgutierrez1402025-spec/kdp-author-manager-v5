<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiobookQualityCheck extends Model
{
    protected $guarded = [];

    protected $casts = ['evidence' => 'array', 'checked_at' => 'datetime'];

    public function audiobookEdition(): BelongsTo
    {
        return $this->belongsTo(AudiobookEdition::class);
    }

    public function audioAsset(): BelongsTo
    {
        return $this->belongsTo(AudioAsset::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
