<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeOffAmount extends Model
{
    //

    protected $fillable = [
        'user_id',
        'insert_date',
        'reference_date',
        'time_off_amount',
        'rol_amount',
    ];

    protected $casts = [
        'time_off_amount' => 'decimal:5',
        'rol_amount' => 'decimal:5',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
