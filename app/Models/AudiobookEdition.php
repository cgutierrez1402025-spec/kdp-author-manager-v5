<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AudiobookEdition extends Model
{
    protected $guarded = [];

    protected $casts = ['exclusive' => 'boolean', 'kdp_select_inherited' => 'boolean', 'territories' => 'array', 'published_at' => 'datetime', 'royalty_rate_effective_at' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function workLanguage(): BelongsTo
    {
        return $this->belongsTo(WorkLanguage::class);
    }

    public function manuscriptVersion(): BelongsTo
    {
        return $this->belongsTo(ManuscriptVersion::class);
    }

    public function sourcePublication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'source_publication_id');
    }

    public function narrators(): BelongsToMany
    {
        return $this->belongsToMany(Narrator::class, 'audiobook_narrators')->withPivot(['role', 'external_voice_id', 'sort_order'])->withTimestamps();
    }

    public function productions(): HasMany
    {
        return $this->hasMany(AudiobookProduction::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(AudiobookChapter::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(AudioAsset::class);
    }

    public function pronunciations(): HasMany
    {
        return $this->hasMany(AudiobookPronunciation::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(AudiobookDistribution::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(AudiobookCost::class);
    }

    public function royalties(): HasMany
    {
        return $this->hasMany(AudiobookRoyalty::class);
    }

    public function qualityChecks(): HasMany
    {
        return $this->hasMany(AudiobookQualityCheck::class);
    }
}
