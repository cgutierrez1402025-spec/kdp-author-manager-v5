<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Platform extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function marketplaces(): HasMany
    {
        return $this->hasMany(Marketplace::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }
}
