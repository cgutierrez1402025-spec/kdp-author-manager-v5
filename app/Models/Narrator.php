<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Narrator extends Model
{
    protected $guarded = [];

    protected $casts = ['languages' => 'array', 'voice_attributes' => 'array', 'voice_consent' => 'boolean', 'consent_expires_at' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function audiobookEditions(): BelongsToMany
    {
        return $this->belongsToMany(AudiobookEdition::class, 'audiobook_narrators')->withPivot(['role', 'external_voice_id', 'sort_order'])->withTimestamps();
    }
}
