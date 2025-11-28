<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTravelStep extends Model
{
    //

    protected $fillable = [
        'daily_travel_structure_id',
        'step_number',
        'time_difference',
        'address',
        'city',
        'province',
        'zip_code',
        'latitude',
        'longitude',
    ];

    public function dailyTravelStructure()
    {
        return $this->belongsTo(DailyTravelStructure::class);
    }
}
