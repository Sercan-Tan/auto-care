<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'plate_no',
        'brand',
        'model',
        'year',
        'chassis_no',
        'owner_name',
        'owner_phone',
        'owner_email',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}