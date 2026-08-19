<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributionPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'address',
        'city',
        'province',
        'country',
        'phone',
        'email',
        'website',
        'contact_person',
        'accepts_consignment',
        'accepts_direct_purchase',
        'accepts_events',
        'default_commission_percentage',
        'usual_payment_terms',
        'relationship_status',
        'rating',
        'notes',
    ];

    protected $casts = [
        'accepts_consignment' => 'boolean',
        'accepts_direct_purchase' => 'boolean',
        'accepts_events' => 'boolean',
        'default_commission_percentage' => 'decimal:2',
        'rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'to_location_id');
    }

    public function bookDeliveries(): HasMany
    {
        return $this->hasMany(BookDelivery::class);
    }

    public function distributionVisits(): HasMany
    {
        return $this->hasMany(DistributionVisit::class);
    }
}
