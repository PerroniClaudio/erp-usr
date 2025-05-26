<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessTripTransfer extends Model {
    use HasFactory;

    protected $fillable = [
        'business_trip_id',
        'company_id',
        'date',
        'address',
        'city',
        'province',
        'zip_code',
        'latitude',
        'longitude',
        'vehicle_id',
    ];

    public function businessTrip() {
        return $this->belongsTo(BusinessTrip::class);
    }

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function vehicle() {
        return $this->belongsTo(Vehicle::class);
    }
}
