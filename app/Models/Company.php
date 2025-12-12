<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function users() {
        return $this->belongsToMany(User::class, 'user_companies', 'company_id', 'user_id');
    }

    public function headquarters() {
        return $this->hasMany(Headquarters::class);
    }
}
