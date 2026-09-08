<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'prompt_id',
        'title',
        'author',
        'year',
        'source_type',
        'language_code',
        'url',
        'consulted_at',
        'citation',
        'summary',
        'origin',
        'generated_content',
        'metadata',
        'rights_status',
        'license',
        'reliability',
        'file_path',
        'notes',
    ];

    protected $casts = [
        'consulted_at' => 'date',
        'metadata' => 'array',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(SourceUsage::class);
    }
}
