<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricePerKmUpdate extends Model {
    //

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'price_per_km',
        'update_date',
    ];

    protected $casts = [
        'update_date' => 'date',
        'price_per_km' => 'decimal:4',
    ];

    public function vehicle() {
        return $this->belongsTo(Vehicle::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
