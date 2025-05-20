<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeOffType extends Model {
    //

    protected $fillable = [
        'name'
    ];

    public function timeOffRequest() {
        return $this->hasMany(TimeOffRequest::class, 'time_off_type_id');
    }
}
