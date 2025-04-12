<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'vehicle_id',
        'mileage',
        'service_date',
        'labor_cost',
    ];

    protected $casts = [
        'service_date' => 'date',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }
}