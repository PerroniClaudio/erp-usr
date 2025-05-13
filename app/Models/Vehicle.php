<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Vehicle extends Model {
    //

    use Searchable;

    public function toSearchableArray() {
        return [
            'id' => $this->id,
            'brand' => $this->brand,
            'model' => $this->model,
        ];
    }


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

    public function users() {
        return $this->belongsToMany(User::class)->withPivot([
            'vehicle_type',
            'ownership_type',
            'purchase_type',
            'contract_start_date',
            'contract_end_date',
            'mileage',
            'mileage_update_date'
        ]);
    }
}
