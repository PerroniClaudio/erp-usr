<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Attendance extends Model {
    use HasFactory;

    protected $fillable = [
        "user_id",
        "company_id",
        "inserted_by",
        "date",
        "time_in",
        "time_out",
        "hours",
        "status",
        "attendance_type_id",
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function insertedBy() {
        return $this->belongsTo(User::class, 'inserted_by');
    }


    public function attendanceType() {
        return $this->belongsTo(AttendanceType::class);
    }

    protected function date(): Attribute
    {
        return Attribute::get(function ($value) {
            return $this->parseDateValue($value);
        });
    }

    private function parseDateValue($value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        $valueString = trim((string) $value);
        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d',
            'd/m/Y H:i:s',
            'd/m/Y',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $valueString);
            } catch (\Exception $e) {
                continue;
            }
        }

        return Carbon::parse($valueString);
    }

    public function formattedUserName() {

        $name_parts = explode(' ', $this->user->name);

        $name = str_split($name_parts[0])[0] . '.';
        $lastName = $name_parts[1] ?? '';

        return $name . ' ' . $lastName;
    }
}
