<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDefaultSchedule extends Model
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

    public const TYPES = [
        'work',
        'overtime',
    ];

    protected $fillable = [
        'user_id',
        'day',
        'hour_start',
        'hour_end',
        'total_hours',
        'type',
    ];

    protected $casts = [
        'hour_start' => 'datetime:H:i',
        'hour_end' => 'datetime:H:i',
        'total_hours' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
