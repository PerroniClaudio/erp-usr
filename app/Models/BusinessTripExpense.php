<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessTripExpense extends Model {
    use HasFactory;

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

    private $expenseTypes = [
        'Pasto',
        'Pedaggio',
        'Parcheggio',
        'Trasporto'
    ];

    private $paymentTypes = [
        'Carta di credito aziendale',
        'Carta di credito personale',
        'Bancomat aziendale',
        'Bancomat personale',
        'Anticipo contante',
        'Contante personale',
    ];

    public function expenseType() {
        return $this->expenseTypes[$this->expense_type];
    }

    public function paymentType() {
        return $this->paymentTypes[$this->payment_type];
    }

    public function businessTrip() {
        return $this->belongsTo(BusinessTrip::class);
    }

    public function company() {
        return $this->belongsTo(Company::class);
    }
}
