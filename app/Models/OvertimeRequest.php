<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeRequest extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'date',
        'time_in',
        'time_out',
        'hours',
        'status',
        'batch_id',
        'overtime_type_id',
    ];

    protected $casts = [
        'hours' => 'float',
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
