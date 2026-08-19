<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Work extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id', 'series_id', 'series_number', 'title', 'slug', 'description',
        'title_internal', 'title_public',
        'subtitle', 'author_name', 'pen_name', 'genre', 'subgenre', 'work_type',
        'original_language', 'status', 'target_audience', 'age_recommendation',
        'description_internal', 'description_marketing', 'start_date',
        'planned_publish_date', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'planned_publish_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title_public', 'status', 'author_name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function workLanguages(): HasMany
    {
        return $this->hasMany(WorkLanguage::class);
    }

    public function manuscriptVersions(): HasMany
    {
        return $this->hasMany(ManuscriptVersion::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(Source::class);
    }

    public function prompts(): HasMany
    {
        return $this->hasMany(Prompt::class);
    }
}
