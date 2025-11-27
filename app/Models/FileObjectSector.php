<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileObjectSector extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'acronym',
        'color',
    ];

    public function fileObjects()
    {
        return $this->hasMany(FileObject::class);
    }
}
