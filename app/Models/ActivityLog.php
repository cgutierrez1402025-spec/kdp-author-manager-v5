<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'loggable_type',
        'loggable_id',
        'properties',
    ];

    protected $casts = [
        'properties' => 'json',
        'created_at' => 'timestamp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(string $action, ?string $description = null, $loggable = null, array $properties = []): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'loggable_type' => $loggable ? get_class($loggable) : null,
            'loggable_id' => $loggable ? $loggable->id : null,
            'properties' => $properties,
        ]);
    }
}
