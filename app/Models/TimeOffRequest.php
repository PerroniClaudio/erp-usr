<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeOffRequest extends Model {

    protected $fillable = [
        'user_id',
        'company_id',
        'time_off_type_id',
        'date_from',
        'date_to',
        'status',
        'batch_id'
    ];

    protected $status_names = [
        0 => 'created',
        1 => 'pending',
        2 => 'approved',
        3 => 'rejected'
    ];

    protected $colors = [
        0 => '#F5A7A3', // In pending ma dell'utente attivo
        1 => '#71AAC1', // In pending ma di un altro utente
        2 => '#e73028', // Approvato ma dell'utente attivo
        3 => '#437f97', // Approvato ma di un altro utente
        4 => '#f0ad4e', // Rifiutato ma dell'utente attivo
    ];

    public function type() {
        return $this->belongsTo(TimeOffType::class, 'time_off_type_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getColorForRequest($user_id) {

        if ($user_id == 0) {
            // richiesta di un admin, ritorna il colore principale 

            if ($this->status == 0 || $this->status == 1) {
                return $this->colors[0]; // Pending for active user
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
                return $this->colors[1]; // Pending for another user
            } elseif ($this->status == 2) {
                return $this->colors[3]; // Approved for another user
            }
        }

        return "#000"; // Default color if none match

    }
}
