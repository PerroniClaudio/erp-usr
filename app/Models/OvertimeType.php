<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeType extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'acronym',
    ];

    public function overtimeRequests() {
        return $this->hasMany(OvertimeRequest::class);
    }
}
