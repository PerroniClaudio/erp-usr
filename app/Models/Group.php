<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];

    public function users() {
        return $this->belongsToMany(User::class, 'groups_users', 'group_id', 'user_id');
    }
}
