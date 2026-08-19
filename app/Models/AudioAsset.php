<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudioAsset extends Model
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
