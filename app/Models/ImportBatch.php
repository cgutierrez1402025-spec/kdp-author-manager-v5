<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'import_type', 'report_period', 'source_system', 'original_file_path',
        'original_file_name', 'file_hash', 'detected_format', 'status', 'total_rows',
        'imported_rows', 'skipped_rows', 'error_rows', 'started_at', 'finished_at',
        'processed_by_ai', 'ai_tool_id', 'notes',
    ];

    protected $casts = [
        'report_period' => 'date',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'processed_by_ai' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reportRows(): HasMany
    {
        return $this->hasMany(KdpReportRow::class);
    }

    public function errors(): HasMany
    {
        return $this->hasMany(ImportError::class);
    }
}
