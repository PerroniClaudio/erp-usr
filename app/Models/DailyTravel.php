<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\DailyTravelRouteStep;

class DailyTravel extends Model
{
    use SoftDeletes;

    protected $table = 'daily_travels';

    protected $fillable = [
        'user_id',
        'company_id',
        'daily_travel_structure_id',
        'travel_date',
    ];

    protected $casts = [
        'travel_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function structure()
    {
        return $this->belongsTo(DailyTravelStructure::class, 'daily_travel_structure_id');
    }

    public function routeSteps()
    {
        return $this->hasMany(DailyTravelRouteStep::class)->orderBy('step_number');
    }

}
