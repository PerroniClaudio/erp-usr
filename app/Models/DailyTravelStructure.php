<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTravelStructure extends Model
{
    //

    protected $fillable = [
        'user_id',
        'company_id',
        'vehicle_id',
        'cost_per_km',
        'economic_value',
    ];

    protected $casts = [
        'cost_per_km' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function steps()
    {
        return $this->hasMany(DailyTravelStep::class);
    }
    public function dailyTravels()
    {
        return $this->hasMany(DailyTravel::class);
    }
}
