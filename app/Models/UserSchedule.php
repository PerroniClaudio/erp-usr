<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSchedule extends Model
{
    use HasFactory;

    public const DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    protected $fillable = [
        'user_id',
        'date',
        'day',
        'hour_start',
        'hour_end',
        'total_hours',
        'attendance_type_id',
    ];

    protected $casts = [
        'date' => 'date',
        'hour_start' => 'datetime:H:i',
        'hour_end' => 'datetime:H:i',
        'total_hours' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceType(): BelongsTo
    {
        return $this->belongsTo(AttendanceType::class);
    }
}
