<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IllustrationAnchor extends Model
{
    use HasFactory;

    protected $fillable = [
        'illustration_id',
        'manuscript_version_id',
        'chapter_id',
        'anchor_type',
        'position_type',
        'search_text',
        'search_text_before',
        'search_text_after',
        'css_selector',
        'html_marker',
        'insertion_mode',
        'confidence',
        'status',
        'notes',
        'applied',
        'applied_html_content',
        'applied_version_id',
        'applied_at',
    ];

    protected $casts = [
        'applied' => 'boolean',
        'applied_at' => 'timestamp',
    ];

    public function illustration(): BelongsTo
    {
        return $this->belongsTo(Illustration::class);
    }

    public function manuscriptVersion(): BelongsTo
    {
        return $this->belongsTo(ManuscriptVersion::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function appliedVersion(): BelongsTo
    {
        return $this->belongsTo(ManuscriptVersion::class, 'applied_version_id');
    }

    public function isApplied(): bool
    {
        return $this->applied;
    }
}
