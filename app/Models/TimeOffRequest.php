<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class TimeOffRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'time_off_type_id',
        'date_from',
        'date_to',
        'status',
        'batch_id',
        'denial_reason',
    ];

    protected $status_names = [
        0 => 'created',
        1 => 'pending',
        2 => 'approved',
        3 => 'rejected',
    ];

    protected $colors = [
        0 => '#F5A7A3', // In pending ma dell'utente attivo
        1 => '#71AAC1', // In pending ma di un altro utente
        2 => '#22c55e', // Approvato ma dell'utente attivo
        3 => '#437f97', // Approvato ma di un altro utente
        4 => '#f0ad4e', // Rifiutato ma dell'utente attivo
    ];

    public function type()
    {
        return $this->belongsTo(TimeOffType::class, 'time_off_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getColorForRequest($user_id)
    {

        if ($user_id == 0) {
            // richiesta di un admin, ritorna il colore principale

            if ($this->status == 0 || $this->status == 1) {
                return $this->user->color; // Pending for active user
            } elseif ($this->status == 2) {
                return $this->colors[2]; // Approved for active user
            } elseif ($this->status == 3) {
                return $this->colors[4]; // Rejected for active user
            }
        }

        if ($this->user_id == $user_id) {
            if ($this->status == 0 || $this->status == 1) {
                return $this->colors[0]; // Pending for active user
            } elseif ($this->status == 2) {
                return $this->colors[2]; // Approved for active user
            } elseif ($this->status == 3) {
                return $this->colors[4]; // Rejected for active user
            }
        } else {
            if ($this->status == 0 || $this->status == 1) {
                return $this->user->color;
            } elseif ($this->status == 2) {
                return $this->colors[3]; // Approved for another user
            }
        }

        return '#000'; // Default color if none match

    }

    public function getDateFromAttribute($value)
    {
        return $this->parseDateValue($value);
    }

    public function getDateToAttribute($value)
    {
        return $this->parseDateValue($value);
    }

    private function parseDateValue($value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        $valueString = trim((string) $value);
        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd/m/Y',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $valueString);
            } catch (\Exception $e) {
                continue;
            }
        }

        return Carbon::parse($valueString);
    }

    public function formattedUserName()
    {

        $name_parts = explode(' ', $this->user->name);

        $name = str_split($name_parts[0])[0].'.';
        $lastName = $name_parts[1] ?? '';

        return $name.' '.$lastName;
    }

    public function scopeExcludeMidnightOnly($query)
    {
        return $query->where(function ($q) {
            $q->whereTime('date_from', '<>', '00:00:00')
                ->orWhereTime('date_to', '<>', '00:00:00');
        });
    }

    public function isInvalidDate(): bool
    {
        $start = Carbon::parse($this->date_from);
        $end = Carbon::parse($this->date_to);

        return $start->format('H:i:s') === '00:00:00' && $end->format('H:i:s') === '00:00:00';
    }
}
