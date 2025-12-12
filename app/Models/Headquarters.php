<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Headquarters extends Model
{
    //

    protected $fillable = [
        'name',
        'address',
        'city',
        'province',
        'zip_code',
        'latitude',
        'longitude',
        'company_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'headquarters_user', 'headquarters_id', 'user_id')
            ->withTimestamps();
    }
}
