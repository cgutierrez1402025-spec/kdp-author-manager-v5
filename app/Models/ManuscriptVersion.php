<?php

namespace App\Models;

use App\Services\VersionManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ManuscriptVersion extends Model
{
    use HasFactory, LogsActivity;

    protected $attributes = [
        'is_candidate' => false,
        'is_final' => false,
        'is_published' => false,
    ];

    protected $fillable = [
        'work_id',
        'work_language_id',
        'parent_version_id',
        'edition_id',
        'version_number',
        'name',
        'status',
        'html_content',
        'file_path',
        'file_hash',
        'word_count',
        'chapter_count',
        'image_count',
        'change_summary',
        'is_candidate',
        'is_final',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'is_candidate' => 'boolean',
        'is_final' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'timestamp',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'version_number', 'is_final', 'is_published'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::created(function ($version) {
            $manager = app(VersionManager::class);
            $manager->processVersion($version);
        });

        static::updated(function ($version) {
            if ($version->wasChanged('html_content')) {
                $manager = app(VersionManager::class);
                $manager->processVersion($version);
            }
        });
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function workLanguage(): BelongsTo
    {
        return $this->belongsTo(WorkLanguage::class);
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentVersion(): BelongsTo
    {
        return $this->belongsTo(ManuscriptVersion::class, 'parent_version_id');
    }

    public function childVersions(): HasMany
    {
        return $this->hasMany(ManuscriptVersion::class, 'parent_version_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function createChildVersion(array $data = []): ManuscriptVersion
    {
        return static::create(array_merge([
            'work_id' => $this->work_id,
            'work_language_id' => $this->work_language_id,
            'parent_version_id' => $this->id,
            'version_number' => $this->nextVersionNumber(),
            'status' => 'draft',
            'is_candidate' => false,
            'is_final' => false,
            'is_published' => false,
        ], $data));
    }

    public function nextVersionNumber(): string
    {
        $latest = static::where('work_id', $this->work_id)
            ->where('work_language_id', $this->work_language_id)
            ->orderByDesc('id')
            ->first();

        return $latest ? (string) (((int) $latest->version_number) + 1) : '1';
    }

    public function markAsFinal(): bool
    {
        return $this->update([
            'is_final' => true,
            'is_candidate' => false,
            'status' => 'final',
        ]);
    }

    public function markAsPublished(): bool
    {
        return $this->update([
            'is_published' => true,
            'is_final' => true,
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
