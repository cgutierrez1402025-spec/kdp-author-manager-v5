<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourceUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'work_id',
        'manuscript_version_id',
        'chapter_id',
        'fragment',
        'usage_type',
        'notes',
        'verified',
    ];

    protected $casts = [
        'verified' => 'boolean',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function manuscriptVersion(): BelongsTo
    {
        return $this->belongsTo(ManuscriptVersion::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
