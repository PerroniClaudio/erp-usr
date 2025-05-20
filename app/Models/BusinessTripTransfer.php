<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTripTransfer extends Model {
    //

    protected $fillable = [
        'business_trip_id',
        'company_id',
        'date',
        'address',
        'city',
        'province',
        'zip_code',
        'latitude',
        'longitude'
    ];

    public function businessTrip() {
        return $this->belongsTo(BusinessTrip::class);
    }

    public function company() {
        return $this->belongsTo(Company::class);
    }
}
