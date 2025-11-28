<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class FileObject extends Model
{
    //
    use SoftDeletes;
    use Searchable;
    protected $fillable = [
       'user_id',
       'file_object_sector_id',
        'protocol_id',
        'protocol_number',
        'protocol_sequence',
        'protocol_year',
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
        'valid_at',
    ];

    protected $casts = [
        'valid_at' => 'date',
    ];

    public function sector()
    {
        return $this->belongsTo(FileObjectSector::class, 'file_object_sector_id');
    }

    public function protocol()
    {
        return $this->belongsTo(Protocol::class, 'protocol_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isFolder()
    {
        return $this->type === 'folder';
    }

    public function humanFileSize()
    {
        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->type === 'file';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'uploaded_name' => $this->uploaded_name,
            'document_type' => $this->document_type,
            'mime_type' => $this->mime_type,
            'storage_path' => $this->storage_path,
            'protocol_number' => $this->protocol_number,
            'protocol_year' => $this->protocol_year,
            'sector' => $this->sector?->name,
            'sector_acronym' => $this->sector?->acronym,
            'valid_at' => $this->valid_at?->toDateString(),
        ];
    }
}
