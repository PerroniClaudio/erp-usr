<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserScheduleChangeRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DENIED = 'denied';

    protected $fillable = [
        'user_id',
        'week_start',
        'week_end',
        'schedule',
        'status',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'schedule' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
