<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'manuscript_version_id',
        'work_id',
        'chapter_order',
        'level',
        'title',
        'slug',
        'html_id',
        'start_position',
        'end_position',
        'word_count',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_position' => 'integer',
        'end_position' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($chapter) {
            if ($chapter->start_position !== null && $chapter->end_position !== null) {
                $chapter->word_count = $chapter->end_position - $chapter->start_position;
            }
        });
    }

    public function manuscriptVersion(): BelongsTo
    {
        return $this->belongsTo(ManuscriptVersion::class);
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }
}
