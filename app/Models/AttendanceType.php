<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceType extends Model {
    use HasFactory;

    protected $fillable = [
        "name",
        "acronym",
    ];

    public function attendances() {
        return $this->hasMany(Attendance::class);
    }
}
