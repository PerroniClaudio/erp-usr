<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model {
    //

    protected $fillable = [
        'brand',
        'model',
        'price_per_km',
        'fringe_benefit_25',
        'fringe_benefit_30',
        'fringe_benefit_50',
        'fringe_benefit_60',
        'last_update'
    ];

    protected $casts = [
        'last_update' => 'datetime',
    ];
}
