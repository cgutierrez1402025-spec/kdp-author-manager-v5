<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Illustration extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'work_language_id',
        'title',
        'description',
        'image_type',
        'file_original',
        'file_optimized',
        'thumbnail',
        'format',
        'width',
        'height',
        'resolution',
        'ai_tool_id',
        'prompt_id',
        'rights_status',
        'license',
        'status',
        'approved',
        'usage_count',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'usage_count' => 'integer',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function workLanguage(): BelongsTo
    {
        return $this->belongsTo(WorkLanguage::class);
    }

    public function aiTool(): BelongsTo
    {
        return $this->belongsTo(AiTool::class);
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    public function anchors(): HasMany
    {
        return $this->hasMany(IllustrationAnchor::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(IllustrationVersion::class);
    }
}
