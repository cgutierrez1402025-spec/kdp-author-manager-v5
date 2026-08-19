<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'task_type',
        'preferred_ai_tool_id',
        'notes',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function preferredAiTool(): BelongsTo
    {
        return $this->belongsTo(AiTool::class, 'preferred_ai_tool_id');
    }

    public function prompts(): HasMany
    {
        return $this->hasMany(Prompt::class, 'task_id');
    }
}
