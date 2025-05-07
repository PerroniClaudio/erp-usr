<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model {
    //

    protected $fillable = [
        'name',
    ];

    public function users() {
        return $this->belongsToMany(User::class, 'user_companies', 'company_id', 'user_id');
    }
}
