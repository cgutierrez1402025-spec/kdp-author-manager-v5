<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalibreImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_batch_id',
        'calibre_book_id',
        'title',
        'author',
        'series',
        'series_index',
        'language_code',
        'tags',
        'opf_path',
        'cover_path',
        'available_formats_json',
        'matched_work_id',
        'status',
    ];

    protected $casts = [
        'available_formats_json' => 'array',
    ];

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function matchedWork(): BelongsTo
    {
        return $this->belongsTo(Work::class, 'matched_work_id');
    }

    public function getAvailableFormatsAttribute(): array
    {
        return $this->available_formats_json ?? [];
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnmatched($query)
    {
        return $query->whereNull('matched_work_id');
    }

    public function createWork(): ?Work
    {
        $work = Work::create([
            'user_id' => $this->importBatch->user_id,
            'title_internal' => $this->title,
            'title_public' => $this->title,
            'author_name' => $this->author,
            'original_language' => $this->language_code ?? 'en',
            'status' => 'idea',
        ]);

        if ($this->series) {
            $series = Series::firstOrCreate(
                ['title' => $this->series],
                ['user_id' => $this->importBatch->user_id, 'title' => $this->series]
            );
            $work->update(['series_id' => $series->id]);
        }

        $this->update(['matched_work_id' => $work->id, 'status' => 'imported']);

        return $work;
    }
}
