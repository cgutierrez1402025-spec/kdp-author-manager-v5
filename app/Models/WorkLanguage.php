<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkLanguage extends Model
{
    use HasFactory;

    protected $attributes = [
        'ai_translation_used' => false,
    ];

    protected $fillable = [
        'work_id', 'language_code', 'regional_variant', 'translated_title',
        'translated_subtitle', 'translator_name', 'translation_status',
        'ai_translation_used', 'human_review_level', 'notes',
    ];

    protected $casts = [
        'ai_translation_used' => 'boolean',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function manuscriptVersions(): HasMany
    {
        return $this->hasMany(ManuscriptVersion::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }
}
