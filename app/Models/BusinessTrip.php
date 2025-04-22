<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTrip extends Model {
    //

    protected $fillable = [
        'user_id',
        'date_from',
        'date_to',
        'status',
        'expense_type',
        'code',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function expenses() {
        return $this->hasMany(BusinessTripExpense::class);
    }

    public function transfers() {
        return $this->hasMany(BusinessTripTransfer::class);
    }
}
