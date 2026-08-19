<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'name',
        'description',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class);
    }

    public function getProgressPercentageAttribute(): int
    {
        $total = $this->items()->count();
        if ($total === 0) {
            return 0;
        }

        $checked = $this->items()->where('is_checked', true)->count();

        return (int) round(($checked / $total) * 100);
    }
}
