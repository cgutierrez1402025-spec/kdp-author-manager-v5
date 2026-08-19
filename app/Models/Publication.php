<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Publication extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'work_id', 'work_language_id', 'manuscript_version_id', 'platform_id',
        'marketplace_id', 'format', 'external_identifier', 'public_url', 'status',
        'price', 'currency', 'territories', 'isbn', 'asin', 'published_at', 'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'price', 'asin'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function workLanguage(): BelongsTo
    {
        return $this->belongsTo(WorkLanguage::class);
    }

    public function manuscriptVersion(): BelongsTo
    {
        return $this->belongsTo(ManuscriptVersion::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function royaltyEntries(): HasMany
    {
        return $this->hasMany(RoyaltyEntry::class);
    }

    public function kdpMetadata(): HasOne
    {
        return $this->hasOne(KdpMetadata::class);
    }
}
