<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subgenre extends Model
{
    use HasFactory;

    protected $fillable = ['genre_id', 'name', 'slug', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function works(): BelongsToMany
    {
        return $this->belongsToMany(Work::class);
    }
}
