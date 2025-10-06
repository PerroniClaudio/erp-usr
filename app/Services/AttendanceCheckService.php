<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\FailedAttendance;
use App\Models\TimeOffRequest;
use App\Models\User;
use Carbon\Carbon;

class AttendanceCheckService
{
    /**
     * Controlla se un utente ha completato le 8 ore lavorative necessarie
     * per le precedenti giornate lavorative dalla data corrente.
     */
    public function checkMissingWorkHours(User $user): array
    {
        $missingDays = [];
        $today = Carbon::today();
        
        // Controlla le ultime 8 giornate lavorative (escludendo weekend)
        $workDaysToCheck = 8;
        $currentDate = $today->copy()->subDay(); // Inizia da ieri
        $checkedDays = 0;
        
        while ($checkedDays < $workDaysToCheck) {
            // Salta i weekend (sabato = 6, domenica = 0)
            if ($currentDate->dayOfWeek !== Carbon::SATURDAY && $currentDate->dayOfWeek !== Carbon::SUNDAY) {
                $dateString = $currentDate->format('Y-m-d');
                
                // Controlla se per questo giorno sono già presenti FailedAttendance non risolte
                $existingFailedAttendance = FailedAttendance::where('user_id', $user->id)
                    ->where('date', $dateString)
                    ->where('status', '!=', 2) // Non considerare quelle già approvate
                    ->exists();
                
                if (!$existingFailedAttendance) {
                    $totalHours = $this->calculateTotalWorkingHours($user, $dateString);
                    
                    if ($totalHours < 8) {
                        $missingDays[] = [
                            'date' => $dateString,
                            'total_hours' => $totalHours,
                            'missing_hours' => 8 - $totalHours,
                        ];
                    }
                }
                
                $checkedDays++;
            }
            
            $currentDate->subDay();
        }
        
        return $missingDays;
    }
    
    /**
     * Calcola il totale delle ore lavorative per un utente in una specifica data,
     * considerando sia presenze che permessi/ferie.
     */
    private function calculateTotalWorkingHours(User $user, string $date): float
    {
        $totalHours = 0;
        
        // Calcola ore dalle presenze
        $attendances = Attendance::where('user_id', $user->id)
            ->where('date', $date)
            ->where('status', '!=', 0) // Esclude presenze non approvate
            ->get();
            
        $totalHours += $attendances->sum('hours');
        
        // Calcola ore dai permessi/ferie approvati
        $timeOffRequests = TimeOffRequest::where('user_id', $user->id)
            ->where('status', 2) // Solo richieste approvate
            ->where('date_from', '<=', $date)
            ->where('date_to', '>=', $date)
            ->get();
            
        foreach ($timeOffRequests as $request) {
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);
            $targetDate = Carbon::parse($date);
            
            // Se la richiesta è solo per un giorno
            if ($dateFrom->isSameDay($dateTo)) {
                $hours = $dateTo->diffInHours($dateFrom);
                $totalHours += $hours;
            } else {
                // Se la richiesta copre più giorni, considera 8 ore per ogni giorno coperto
                if ($targetDate->isSameDay($dateFrom) || $targetDate->isSameDay($dateTo) || 
                    ($targetDate->isAfter($dateFrom) && $targetDate->isBefore($dateTo))) {
                    $totalHours += 8;
                }
            }
        }
        
        return $totalHours;
    }
    
    /**
     * Crea automaticamente record FailedAttendance per i giorni con ore mancanti.
     */
    public function createFailedAttendancesForMissingHours(User $user, array $missingDays): array
    {
        $createdRecords = [];
        
        foreach ($missingDays as $dayData) {
            $failedAttendance = FailedAttendance::create([
                'user_id' => $user->id,
                'date' => $dayData['date'],
                'status' => 0, // Da giustificare
                'requested_hours' => $dayData['missing_hours'],
            ]);
            
            $createdRecords[] = $failedAttendance;
        }
        
        return $createdRecords;
    }
    
    /**
     * Esegue il controllo completo e crea i record necessari.
     */
    public function performLoginAttendanceCheck(User $user): array
    {
        $missingDays = $this->checkMissingWorkHours($user);
        
        if (!empty($missingDays)) {
            return $this->createFailedAttendancesForMissingHours($user, $missingDays);
        }
        
        return [];
    }
}