<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportSession extends Model
{
    protected $fillable = ['user_id', 'status', 'total_files', 'completed_files', 'failed_files', 'duplicate_files', 'imported_rows', 'skipped_rows', 'error_rows', 'started_at', 'finished_at', 'notes'];

    protected $casts = ['started_at' => 'datetime', 'finished_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ImportBatch::class)->orderBy('processing_order');
    }
}
