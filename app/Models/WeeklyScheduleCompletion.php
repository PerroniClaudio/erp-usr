<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyScheduleCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'week_start',
        'week_end',
        'total_users',
        'completed_users',
        'completed_at',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'completed_at' => 'datetime',
    ];

    public function scopeForWeek($query, $weekStart)
    {
        return $query->whereDate('week_start', $weekStart);
    }

    public function getIsCompletedAttribute(): bool
    {
        return ! is_null($this->completed_at);
    }
}
