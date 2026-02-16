<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTravelAdditionalExpense extends Model
{
    protected $fillable = [
        'daily_travel_id',
        'user_id',
        'description',
        'amount',
        'occurred_at',
        'proof_file_path',
        'proof_file_name',
        'proof_file_mime_type',
        'proof_file_size',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    public function dailyTravel()
    {
        return $this->belongsTo(DailyTravel::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
