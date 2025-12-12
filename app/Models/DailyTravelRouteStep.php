<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Headquarters;

class DailyTravelRouteStep extends Model
{
    protected $fillable = [
        'daily_travel_id',
        'headquarter_id',
        'step_number',
        'name',
        'address',
        'city',
        'province',
        'zip_code',
        'latitude',
        'longitude',
    ];

    public function dailyTravel()
    {
        return $this->belongsTo(DailyTravel::class);
    }

    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class);
    }
}
