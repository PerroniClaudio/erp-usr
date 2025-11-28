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
        'price_per_km' => 'decimal:4',
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

    public function mileageUpdates() {
        return $this->hasMany(MileageUpdate::class);
    }

    public function pricePerKmUpdates() {
        return $this->hasMany(PricePerKmUpdate::class);
    }

    public function businessTripsTransfers() {
        return $this->belongsToMany(BusinessTripTransfer::class);
    }

    public function setPricePerKmAttribute($value) {
        if ($value === null) {
            $this->attributes['price_per_km'] = null;

            return;
        }

        $this->attributes['price_per_km'] = round((float) $value, 4);
    }

    protected static function booted() {
        static::created(function (Vehicle $vehicle) {
            if ($vehicle->price_per_km !== null) {
                $vehicle->pricePerKmUpdates()->create([
                    'user_id' => auth()->id(),
                    'price_per_km' => $vehicle->price_per_km,
                    'update_date' => $vehicle->last_update?->toDateString() ?? now()->toDateString(),
                ]);
            }
        });

        static::updated(function (Vehicle $vehicle) {
            if ($vehicle->wasChanged('price_per_km') && $vehicle->price_per_km !== null) {
                $vehicle->pricePerKmUpdates()->create([
                    'user_id' => auth()->id(),
                    'price_per_km' => $vehicle->price_per_km,
                    'update_date' => $vehicle->last_update?->toDateString() ?? now()->toDateString(),
                ]);
            }
        });
    }
}
