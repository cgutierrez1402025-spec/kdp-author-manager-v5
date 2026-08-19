<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KdpMetadata extends Model
{
    use HasFactory;

    protected $table = 'kdp_metadata';

    protected $fillable = [
        'publication_id',
        'title',
        'subtitle',
        'author',
        'contributors',
        'series_name',
        'series_number',
        'description',
        'keywords',
        'categories',
        'age_range',
        'rights',
        'ai_declaration',
    ];

    protected $casts = [
        'contributors' => 'array',
        'categories' => 'array',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function getContributorListAttribute(): string
    {
        if (! $this->contributors) {
            return '';
        }

        return implode(', ', array_column($this->contributors, 'name'));
    }

    public function getCategoryListAttribute(): string
    {
        if (! $this->categories) {
            return '';
        }

        return implode(', ', $this->categories);
    }
}
