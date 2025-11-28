<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\FileObject;
use App\Models\FileObjectSector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Protocol extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'acronym',
        'counter',
        'counter_year',
    ];

    public function fileObjects()
    {
        return $this->hasMany(FileObject::class);
    }

    /**
     * Generate the next protocol number for the given sector and validity date.
     * Uses a transaction to avoid collisions when multiple uploads happen together.
     */
    public function generateNumberForSector(FileObjectSector $sector, Carbon $validAt): array
    {
        return DB::transaction(function () use ($sector, $validAt) {
            $year = $validAt->year;

            /** @var Protocol $protocol */
            $protocol = Protocol::lockForUpdate()->findOrFail($this->id);

            if ($protocol->counter_year !== $year) {
                $protocol->counter = 1;
                $protocol->counter_year = $year;
            }

            $sequence = $protocol->counter;
            $protocol->counter = $sequence + 1;
            $protocol->save();

            $protocolPrefix = strtoupper($protocol->acronym);
            $sectorAcronym = strtoupper($sector->acronym);

            $protocolNumber = sprintf('%s-%s-%s-%04d', $protocolPrefix, $sectorAcronym, $year, $sequence);

            return [$protocolNumber, $sequence, $year];
        });
    }
}
