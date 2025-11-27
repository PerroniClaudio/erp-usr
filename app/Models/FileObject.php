<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileObject extends Model
{
    //
    protected $fillable = [
       'user_id',
        'file_object_sector_id',
        'logical_key',
        'version',
        'type',
        'name',
        'uploaded_name',
        'document_type',
        'mime_type',
        'file_size',
        'storage_path',
        'is_public',
        'uploaded_by_system',
        'processed_at',
        'expires_at',
    ];

    public function sector()
    {
        return $this->belongsTo(FileObjectSector::class, 'file_object_sector_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
