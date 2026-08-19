<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoyaltyEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id', 'year', 'month', 'paid_units', 'free_units',
        'kenp_pages', 'royalty_ebook', 'royalty_paperback', 'royalty_hardcover',
        'royalty_kenp', 'total_royalty', 'currency', 'source_file', 'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'paid_units' => 'integer',
        'free_units' => 'integer',
        'kenp_pages' => 'integer',
        'royalty_ebook' => 'decimal:2',
        'royalty_paperback' => 'decimal:2',
        'royalty_hardcover' => 'decimal:2',
        'royalty_kenp' => 'decimal:2',
        'total_royalty' => 'decimal:2',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        $months = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];

        return "{$months[$this->month]} {$this->year}";
    }
}
