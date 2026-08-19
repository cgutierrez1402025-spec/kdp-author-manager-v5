<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalPrintRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'edition_id',
        'work_language_id',
        'format',
        'print_date',
        'printer_name',
        'copies_printed',
        'unit_cost',
        'total_cost',
        'recommended_retail_price',
        'notes',
    ];

    protected $casts = [
        'print_date' => 'date',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'recommended_retail_price' => 'decimal:2',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function workLanguage(): BelongsTo
    {
        return $this->belongsTo(WorkLanguage::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'print_run_id');
    }

    public function getTotalCostCalculatedAttribute(): float
    {
        return $this->copies_printed * ($this->unit_cost ?? 0);
    }
}
