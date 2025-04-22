<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTripExpense extends Model {
    //

    protected $fillable = [
        'business_trip_id',
        'company_id',
        'payment_type',
        'expense_type',
        'amount',
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
