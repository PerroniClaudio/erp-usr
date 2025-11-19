<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyScheduleApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'week_start',
        'week_end',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'approved_at' => 'datetime',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
