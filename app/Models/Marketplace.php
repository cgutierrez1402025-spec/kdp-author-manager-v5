<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Marketplace extends Model
{
    use HasFactory;

    protected $fillable = ['platform_id', 'code', 'name', 'currency'];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
