<?php

namespace App\Models;

use App\Models\DailyTravelRouteStep;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyTravel extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';

    protected $table = 'daily_travels';

    protected $fillable = [
        'user_id',
        'daily_travel_structure_id',
        'travel_date',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function structure()
    {
        return $this->belongsTo(DailyTravelStructure::class, 'daily_travel_structure_id');
    }

    public function routeSteps()
    {
        return $this->hasMany(DailyTravelRouteStep::class)->orderBy('step_number');
    }

    public function additionalExpenses()
    {
        return $this->hasMany(DailyTravelAdditionalExpense::class)->orderByDesc('occurred_at');
    }

    public function isApproved(): bool
    {
        return (bool) $this->approved_at;
    }

    public function approvalStatus(): string
    {
        return $this->isApproved() ? self::STATUS_APPROVED : self::STATUS_PENDING;
    }
}
