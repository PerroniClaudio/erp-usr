<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedAttendance extends Model {
    //

    protected $fillable = [
        'user_id',
        'date',
        'status',
        'reason',
        'requested_type',
        'requested_hours',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
