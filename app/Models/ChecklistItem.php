<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_id',
        'item',
        'is_checked',
        'checked_by',
        'checked_at',
        'order',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
        'checked_at' => 'timestamp',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function markAsChecked(int $userId): void
    {
        $this->update([
            'is_checked' => true,
            'checked_by' => $userId,
            'checked_at' => now(),
        ]);
    }

    public function markAsUnchecked(): void
    {
        $this->update([
            'is_checked' => false,
            'checked_by' => null,
            'checked_at' => null,
        ]);
    }
}
