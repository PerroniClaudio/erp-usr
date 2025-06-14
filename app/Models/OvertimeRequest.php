<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'date',
        'time_in',
        'time_out',
        'hours',
        'status',
        'batch_id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function overtimeType() {
        return $this->belongsTo(OvertimeType::class);
    }
}
