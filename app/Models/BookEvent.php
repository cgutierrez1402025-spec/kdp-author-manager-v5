<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'event_type',
        'event_date',
        'start_time',
        'end_time',
        'location_name',
        'address',
        'city',
        'province',
        'country',
        'organizer',
        'contact_person',
        'phone',
        'email',
        'expected_attendance',
        'actual_attendance',
        'status',
        'notes',
    ];

    protected $casts = [
        'event_date' => 'date',
        'expected_attendance' => 'integer',
        'actual_attendance' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventBooks(): HasMany
    {
        return $this->hasMany(EventBook::class, 'event_id');
    }

    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->where('event_date', '>=', now())
            ->where('event_date', '<=', now()->addDays($days));
    }

    public function getTotalIncomeAttribute(): float
    {
        return $this->eventBooks->sum('income_amount');
    }

    public function getTotalCopiesSoldAttribute(): int
    {
        return $this->eventBooks->sum('copies_sold');
    }
}
