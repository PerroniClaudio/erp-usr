<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedAttendance extends Model {
    use HasFactory;
    //

    protected $fillable = [
        'user_id',
        'date',
        'status',
        'reason',
        'requested_type',
        'requested_hours',
        'request_type',
        'requested_time_in_morning',
        'requested_time_out_morning',
        'requested_time_in_afternoon',
        'requested_time_out_afternoon',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getTotalRequestedHours() {
        if ($this->request_type == 1) {
            // Richiesta presenza - calcola le ore totali
            $morningHours = 0;
            $afternoonHours = 0;

            if ($this->requested_time_in_morning && $this->requested_time_out_morning) {
                $morningStart = \Carbon\Carbon::createFromFormat('H:i', $this->requested_time_in_morning);
                $morningEnd = \Carbon\Carbon::createFromFormat('H:i', $this->requested_time_out_morning);
                $morningHours = $morningEnd->diffInHours($morningStart, true);
            }

            if ($this->requested_time_in_afternoon && $this->requested_time_out_afternoon) {
                $afternoonStart = \Carbon\Carbon::createFromFormat('H:i', $this->requested_time_in_afternoon);
                $afternoonEnd = \Carbon\Carbon::createFromFormat('H:i', $this->requested_time_out_afternoon);
                $afternoonHours = $afternoonEnd->diffInHours($afternoonStart, true);
            }

            return $morningHours + $afternoonHours;
        }

        return $this->requested_hours;
    }
}
