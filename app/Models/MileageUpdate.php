<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MileageUpdate extends Model {
    //

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'mileage',
        'update_date',
    ];

    public function vehicle() {
        return $this->belongsTo(Vehicle::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
