<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class DailyTravelStructure extends Model
{
    //

    protected $fillable = [
        'user_id',
        'company_id',
        'vehicle_id',
        'cost_per_km',
    ];

    protected $casts = [
        'cost_per_km' => 'decimal:4',
    ];

    protected function economicValue(): Attribute
    {
        return Attribute::get(function () {
            if ($this->relationLoaded('steps')) {
                return (float) round($this->steps->sum(fn ($step) => (float) $step->economic_value), 2);
            }

            return (float) round($this->steps()->sum('economic_value'), 2);
        });
    }

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
