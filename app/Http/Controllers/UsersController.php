<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceType;
use App\Models\Company;
use App\Models\Group;
use App\Models\OvertimeRequest;
use App\Models\TimeOffRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\UserDefaultSchedule;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    //

    private $vehicleTypes;

    private $ownershipTypes;

    private $purchaseTypes;

    public function __construct()
    {
        $this->vehicleTypes = collect([
            (object) ['id' => 1, 'name' => 'Autoveicolo'],
            (object) ['id' => 2, 'name' => 'Ciclomotore'],
            (object) ['id' => 3, 'name' => 'Autocarro'],
        ]);

        $this->ownershipTypes = collect([
            (object) ['id' => 1, 'name' => 'Aziendale'],
            (object) ['id' => 2, 'name' => 'Personale'],
        ]);

        $this->purchaseTypes = collect([
            (object) ['id' => 1, 'name' => 'Acquisto'],
            (object) ['id' => 2, 'name' => 'Leasing'],
            (object) ['id' => 3, 'name' => 'Noleggio a breve termine'],
            (object) ['id' => 4, 'name' => 'Noleggio a lungo termine'],
        ]);
    }

    public function index()
    {
        $users = User::all();

        return view('admin.personnel.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        $user->load([
            'defaultSchedules' => function ($query) {
                $query->orderByRaw("CASE day
                    WHEN 'monday' THEN 1
                    WHEN 'tuesday' THEN 2
                    WHEN 'wednesday' THEN 3
                    WHEN 'thursday' THEN 4
                    WHEN 'friday' THEN 5
                    WHEN 'saturday' THEN 6
                    WHEN 'sunday' THEN 7
                    ELSE 8 END")->orderBy('hour_start');
            },
        ]);

        $dayLabels = [
            'monday' => 'Lunedì',
            'tuesday' => 'Martedì',
            'wednesday' => 'Mercoledì',
            'thursday' => 'Giovedì',
            'friday' => 'Venerdì',
            'saturday' => 'Sabato',
            'sunday' => 'Domenica',
        ];

        $scheduleTypes = [
            'work' => 'Lavoro',
            'overtime' => 'Straordinario',
        ];

        return view('admin.personnel.users.edit', compact('user', 'dayLabels', 'scheduleTypes'));
    }

    public function showDefaultSchedule(User $user)
    {
        $referenceMonday = Carbon::now()->startOfWeek(Carbon::MONDAY);

        $scheduleEvents = $user->defaultSchedules()
            ->with('attendanceType')
            ->orderByRaw("CASE day
                WHEN 'monday' THEN 1
                WHEN 'tuesday' THEN 2
                WHEN 'wednesday' THEN 3
                WHEN 'thursday' THEN 4
                WHEN 'friday' THEN 5
                WHEN 'saturday' THEN 6
                WHEN 'sunday' THEN 7
                ELSE 8 END")
            ->orderBy('hour_start')
            ->get()
            ->map(function ($item) use ($referenceMonday) {
                $dayOffsets = [
                    'monday' => 0,
                    'tuesday' => 1,
                    'wednesday' => 2,
                    'thursday' => 3,
                    'friday' => 4,
                    'saturday' => 5,
                    'sunday' => 6,
                ];

                $date = $referenceMonday->copy()->addDays($dayOffsets[$item->day] ?? 0)->toDateString();

                $attendance = $item->attendanceType;

                return [
                    'id' => $item->id,
                    'title' => $attendance?->name ?? 'Fascia',
                    'start' => "{$date} {$item->hour_start}",
                    'end' => "{$date} {$item->hour_end}",
                    'attendance_type_id' => $item->attendance_type_id,
                    'display' => 'block',
                ];
            });

        $attendanceTypes = AttendanceType::orderBy('name')->get();
        $defaultAttendanceTypeId = $attendanceTypes->first()?->id;
        $colorPalette = ['#60a5fa', '#34d399', '#fbbf24', '#a78bfa', '#e73028', '#f472b6', '#2dd4bf', '#437f97'];
        $paletteCount = count($colorPalette);
        $attendanceTypesPayload = $attendanceTypes->values()->map(function ($type, $index) use ($colorPalette, $paletteCount) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'acronym' => $type->acronym,
                'color' => $colorPalette[$index % $paletteCount],
            ];
        });

        return view('admin.personnel.users.default-schedule', [
            'user' => $user,
            'scheduleEvents' => $scheduleEvents,
            'initialDate' => $referenceMonday->toDateString(),
            'schedules' => $user->defaultSchedules,
            'attendanceTypes' => $attendanceTypes,
            'defaultAttendanceTypeId' => $defaultAttendanceTypeId,
            'attendanceTypesPayload' => $attendanceTypesPayload,
        ]);
    }

    public function exportPdf(User $user, Request $request)
    {
        $request->validate([
            'mese' => 'required|string',
            'anno' => 'required|integer',
        ]);

        $mese = $request->mese;
        $anno = $request->anno;

        // Otteniamo i dati per il PDF
        $mesiMap = [
            'Gennaio' => 1,
            'Febbraio' => 2,
            'Marzo' => 3,
            'Aprile' => 4,
            'Maggio' => 5,
            'Giugno' => 6,
            'Luglio' => 7,
            'Agosto' => 8,
            'Settembre' => 9,
            'Ottobre' => 10,
            'Novembre' => 11,
            'Dicembre' => 12,
        ];

        $meseNumero = $mesiMap[$mese];
        $primoGiorno = Carbon::createFromDate($anno, $meseNumero, 1)->startOfDay();
        $ultimoGiorno = Carbon::createFromDate($anno, $meseNumero, 1)->endOfMonth()->endOfDay();

        $anomaliesData = $this->getAnomaliesData($user, $primoGiorno, $ultimoGiorno);

        if ($anomaliesData['hasAnomalies']) {
            // Se ci sono anomalie, mostra la pagina delle anomalie
            return view('admin.personnel.users.anomalies', [
                'user' => $user,
                'mese' => $mese,
                'anno' => $anno,
                'anomaliesData' => $anomaliesData,
                'primoGiorno' => $primoGiorno,
                'ultimoGiorno' => $ultimoGiorno,
            ]);
        } else {
            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$primoGiorno, $ultimoGiorno])
                ->get();

            if ($attendances->isEmpty()) {
                return redirect()->back()->with('error', 'Nessuna presenza trovata per l\'utente selezionato nel mese e anno specificati.');
            }

            // Generiamo il PDF dalla vista
            $pdf = PDF::loadView('cedolini.pdf', [
                'user' => $user,
                'mese' => $mese,
                'anno' => $anno,
                'meseNumero' => $meseNumero,
                'primoGiorno' => $primoGiorno,
                'ultimoGiorno' => $ultimoGiorno,
                'festive' => $this->getFestiveDays(),
            ]);

            // Impostiamo le opzioni del PDF
            $pdf->setPaper('a4', 'landscape');
            $pdf->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

            return $pdf->download('cedolino_'.$user->name.'_'.$mese.'_'.$anno.'.pdf');
        }
    }

    public function exportPresenzePdf(User $user, Request $request)
    {
        $request->validate([
            'mese' => 'required|string',
            'anno' => 'required|integer',
        ]);

        $mese = $request->mese;
        $anno = $request->anno;

        // Otteniamo i dati per il PDF
        $mesiMap = [
            'Gennaio' => 1,
            'Febbraio' => 2,
            'Marzo' => 3,
            'Aprile' => 4,
            'Maggio' => 5,
            'Giugno' => 6,
            'Luglio' => 7,
            'Agosto' => 8,
            'Settembre' => 9,
            'Ottobre' => 10,
            'Novembre' => 11,
            'Dicembre' => 12,
        ];

        $meseNumero = $mesiMap[$mese];
        $primoGiorno = Carbon::createFromDate($anno, $meseNumero, 1)->startOfDay();
        $ultimoGiorno = Carbon::createFromDate($anno, $meseNumero, 1)->endOfMonth()->endOfDay();

        // Otteniamo le presenze dell'utente per il mese specificato
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$primoGiorno, $ultimoGiorno])
            ->orderBy('date')
            ->orderBy('time_in')
            ->get();

        if ($attendances->isEmpty()) {
            return redirect()->back()->with('error', 'Nessuna presenza trovata per l\'utente selezionato nel mese e anno specificati.');
        }

        // Otteniamo le richieste di ferie/permessi dell'utente per il mese specificato
        $timeOffRequests = TimeOffRequest::where('user_id', $user->id)
            ->where('status', '2')
            ->whereBetween('date_from', [$primoGiorno, $ultimoGiorno])
            ->orderBy('date_from')
            ->get();

        // Prepariamo i dati delle presenze per il PDF
        $presenze = $this->preparePresenzeData($attendances, $timeOffRequests, $user, $primoGiorno, $ultimoGiorno);

        // Calcoliamo il riepilogo
        $riepilogo = $this->calcolaRiepilogo($attendances, $timeOffRequests);

        // Calcoliamo i buoni pasto (un buono per ogni giorno lavorato in sede)
        $buoni_pasto = $presenze->filter(function ($presenza) {
            return $presenza->tipologia == 'Lavoro in sede' && ! $presenza->annullata;
        })->groupBy('data')->count();

        // Generiamo il PDF
        $pdf = PDF::loadView('cedolini.presenze_pdf', [
            'user' => $user,
            'mese' => $mese,
            'anno' => $anno,
            'presenze' => $presenze,
            'riepilogo' => $riepilogo,
            'buoni_pasto' => $buoni_pasto,
            'pagina' => 1,
            'totale_pagine' => ceil($presenze->count() / 30), // Assumiamo circa 30 righe per pagina
        ]);

        // Impostiamo le opzioni del PDF
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->download('presenze_'.$user->name.'_'.$mese.'_'.$anno.'.pdf');
    }

    /**
     * Export current logged user's attendances as PDF
     */
    public function exportCurrentUserPresenze(Request $request)
    {
        $user = $request->user();
        return $this->exportPresenzePdf($user, $request);
    }

    public function exportAnomaliesPdf(User $user, Request $request)
    {
        $request->validate([
            'mese' => 'required|string',
            'anno' => 'required|integer',
        ]);

        $mese = $request->mese;
        $anno = $request->anno;

        // Otteniamo i dati per il PDF
        $mesiMap = [
            'Gennaio' => 1,
            'Febbraio' => 2,
            'Marzo' => 3,
            'Aprile' => 4,
            'Maggio' => 5,
            'Giugno' => 6,
            'Luglio' => 7,
            'Agosto' => 8,
            'Settembre' => 9,
            'Ottobre' => 10,
            'Novembre' => 11,
            'Dicembre' => 12,
        ];

        $meseNumero = $mesiMap[$mese];
        $primoGiorno = Carbon::createFromDate($anno, $meseNumero, 1)->startOfDay();
        $ultimoGiorno = Carbon::createFromDate($anno, $meseNumero, 1)->endOfMonth()->endOfDay();

        // Otteniamo i dati delle anomalie
        $anomaliesData = $this->getAnomaliesData($user, $primoGiorno, $ultimoGiorno);

        if (! $anomaliesData['hasAnomalies']) {
            return redirect()->back()->with('error', 'Nessuna anomalia trovata per l\'utente selezionato nel periodo specificato.');
        }

        // Generiamo il PDF
        $pdf = PDF::loadView('cedolini.anomalies_pdf', [
            'user' => $user,
            'mese' => $mese,
            'anno' => $anno,
            'anomaliesData' => $anomaliesData,
            'primoGiorno' => $primoGiorno,
            'ultimoGiorno' => $ultimoGiorno,
        ]);

        // Impostiamo le opzioni del PDF
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->download('anomalie_'.$user->name.'_'.$mese.'_'.$anno.'.pdf');
    }

    private function preparePresenzeData($attendances, $timeOffRequests, $user, $primoGiorno, $ultimoGiorno)
    {
        $presenze = new Collection;

        // Aggiungiamo le presenze
        foreach ($attendances as $attendance) {
            $data = Carbon::parse($attendance->date);
            $giorno_settimana = $this->getGiornoSettimanaItaliano($data->format('D'));

            $tipologia = $attendance->attendanceType->name;

            $presenze->push((object) [
                'id' => $attendance->id,
                'persona' => $user->name,
                'azienda' => $attendance->company->name ?? 'IFORTECH S.R.L.',
                'tipologia' => $tipologia,
                'data' => $data,
                'data_formattata' => $data->format('d-m-Y').' '.$giorno_settimana,
                'ora_inizio' => Carbon::parse($attendance->time_in)->format('H:i'),
                'ora_fine' => Carbon::parse($attendance->time_out)->format('H:i'),
                'ore' => number_format(Carbon::parse($attendance->time_in)->diffInMinutes(Carbon::parse($attendance->time_out)) / 60, 2),
                'annullata' => false,
                'giornata' => null,
            ]);
        }

        // Aggiungiamo le ferie e i permessi
        foreach ($timeOffRequests as $request) {
            $presenze->push((object) [
                'id' => $request->id,
                'persona' => $user->name,
                'azienda' => 'IFORTECH S.R.L.',
                'tipologia' => $request->type->name,
                'data' => $data,
                'data_formattata' => Carbon::parse($request->date_from)->format('d-m-Y'),
                'ora_inizio' => Carbon::parse($request->date_from)->format('H:i'),
                'ora_fine' => Carbon::parse($request->date_to)->format('H:i'),
                'ore' => number_format(Carbon::parse($request->date_from)->diffInMinutes(Carbon::parse($request->date_to)) / 60, 2),
                'annullata' => false,
                'giornata' => null,
            ]);
        }

        // Ordiniamo le presenze per data e ora
        return $presenze->sortBy([
            ['data', 'asc'],
            ['ora_inizio', 'asc'],
        ]);
    }

    private function calcolaRiepilogo($presenze, $permessi)
    {
        $riepilogo = [
            'lavorato' => ['ore' => 0, 'giorni' => 0],
            'straordinario' => ['ore' => 0, 'giorni' => 0],
            'straordinario_notturno' => ['ore' => 0, 'giorni' => 0],
            'straordinario_festivo' => ['ore' => 0, 'giorni' => 0],
            'rol' => ['ore' => 0, 'giorni' => 0],
            'ferie' => ['ore' => 0, 'giorni' => 0],
            'corso_intra' => ['ore' => 0, 'giorni' => 0],
            'corso_extra' => ['ore' => 0, 'giorni' => 0],
            'malattia' => ['ore' => 0, 'giorni' => 0],
        ];

        foreach ($presenze as $presenza) {

            switch ($presenza->attendanceType->name) {
                case 'Lavoro in sede':
                case 'Lavoro c/o cliente':
                case 'Smartworking':
                    $key = 'lavorato';
                    break;
                case 'Straordinario':
                    $key = 'straordinario';
                    break;
                case 'Straordinario Notturno':
                    $key = 'straordinario_notturno';
                    break;
                case 'Straordinario Festivo':
                    $key = 'straordinario_festivo';
                    break;
                case 'Corso intra-lavorativo':
                    $key = 'corso_intra';
                    break;
                case 'Corso extra-lavorativo':
                    $key = 'corso_extra';
                    break;
                case 'Malattia':
                    $key = 'malattia';
                    break;
            }

            $ore = number_format(Carbon::parse($presenza->time_in)->diffInMinutes(Carbon::parse($presenza->time_out)) / 60, 2);

            $riepilogo[$key]['ore'] += floatval(str_replace(',', '.', $ore));
            $riepilogo[$key]['giorni'] += floatval(str_replace(',', '.', $ore)) / 8; // Consideriamo 8 ore come un giorno lavorativo

        }

        foreach ($permessi as $permesso) {

            switch ($permesso->type->name) {
                case 'Ferie':
                    $key = 'ferie';
                    break;
                case 'Rol':
                    $key = 'rol';
                    break;
            }

            $start_date = Carbon::parse($permesso->date_from);
            $end_date = Carbon::parse($permesso->date_to);
            $ore = number_format($start_date->diffInMinutes($end_date) / 60, 2);

            $riepilogo[$key]['ore'] += floatval(str_replace(',', '.', $ore));
            $riepilogo[$key]['giorni'] += floatval(str_replace(',', '.', $ore)) / 8; // Consideriamo 8 ore come un giorno lavorativo
        }

        return $riepilogo;
    }

    private function getGiornoSettimanaItaliano($giornoEn)
    {
        $giorni = [
            'Mon' => 'Lun',
            'Tue' => 'Mar',
            'Wed' => 'Mer',
            'Thu' => 'Gio',
            'Fri' => 'Ven',
            'Sat' => 'Sab',
            'Sun' => 'Dom',
        ];

        return $giorni[$giornoEn] ?? $giornoEn;
    }

    private function verifyAnomalies($user, $dateFrom, $dateTo): bool
    {
        $anomaliesData = $this->getAnomaliesData($user, $dateFrom, $dateTo);

        return ! $anomaliesData['hasAnomalies'];
    }

    /**
     * Ottiene i dati dettagliati delle anomalie per un utente in un periodo
     */
    private function getAnomaliesData($user, $dateFrom, $dateTo): array
    {
        // Calcolo delle settimane nel periodo
        $startDate = Carbon::parse($dateFrom)->startOfWeek();
        $endDate = Carbon::parse($dateTo)->endOfWeek();

        // Ottieni tutte le presenze dell'utente nel periodo
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->with('attendanceType')
            ->get();

        // Ottieni tutti i permessi approvati dell'utente nel periodo
        $timeOffRequests = TimeOffRequest::where('user_id', $user->id)
            ->where('status', 2) // Solo quelli approvati
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('date_from', [$dateFrom, $dateTo])
                    ->orWhereBetween('date_to', [$dateFrom, $dateTo])
                    ->orWhere(function ($q) use ($dateFrom, $dateTo) {
                        $q->where('date_from', '<=', $dateFrom)
                            ->where('date_to', '>=', $dateTo);
                    });
            })
            ->with('type')
            ->get();

        // Giorni festivi
        $festiveDays = $this->getFestiveDays();

        // Calcolo monte ore teorico per il periodo
        $totalExpectedHours = $this->calculateExpectedWorkingHours($dateFrom, $dateTo, $festiveDays);

        // Gruppo per settimane
        $weeklyData = [];
        $currentWeek = $startDate->copy();

        while ($currentWeek->lte($endDate)) {
            $weekStart = $currentWeek->copy()->startOfWeek();
            $weekEnd = $currentWeek->copy()->endOfWeek();

            // Limita ai confini del periodo richiesto
            $actualWeekStart = $weekStart->lt($dateFrom) ? Carbon::parse($dateFrom) : $weekStart;
            $actualWeekEnd = $weekEnd->gt($dateTo) ? Carbon::parse($dateTo) : $weekEnd;

            $weeklyHours = $this->calculateWeeklyHours(
                $user,
                $actualWeekStart,
                $actualWeekEnd,
                $attendances,
                $timeOffRequests,
                $festiveDays
            );

            $weeklyExpectedHours = $this->calculateExpectedWorkingHours($actualWeekStart, $actualWeekEnd, $festiveDays);
            $weeklyDifference = $weeklyHours - $weeklyExpectedHours;

            $weeklyData[] = [
                'week_start' => $actualWeekStart->format('d/m/Y'),
                'week_end' => $actualWeekEnd->format('d/m/Y'),
                'expected_hours' => $weeklyExpectedHours,
                'actual_hours' => $weeklyHours,
                'difference' => $weeklyDifference,
                'limit_exceeded' => $weeklyHours > 40,
                'has_shortage' => $weeklyDifference < -4, // Tolleranza di mezza giornata
                'has_excess' => $weeklyDifference > 4,
            ];

            $currentWeek->addWeek();
        }

        // Calcolo ore totali effettive
        $totalActualHours = $this->calculateTotalActualHours($attendances, $timeOffRequests);
        $totalDifference = $totalActualHours - $totalExpectedHours;

        // Verifica anomalie
        $hasWeeklyAnomalies = collect($weeklyData)->contains(function ($week) {
            return $week['limit_exceeded'] || $week['has_shortage'] || $week['has_excess'];
        });
        $hasMonthlyAnomalies = abs($totalDifference) > 8; // Tolleranza di una giornata

        // Calcola i giorni lavorativi del periodo
        $workingDaysInPeriod = $this->calculateWorkingDays($dateFrom, $dateTo, $festiveDays);

        return [
            'hasAnomalies' => $hasWeeklyAnomalies || $hasMonthlyAnomalies,
            'totalExpectedHours' => $totalExpectedHours,
            'totalActualHours' => $totalActualHours,
            'totalDifference' => $totalDifference,
            'workingDaysInPeriod' => $workingDaysInPeriod,
            'hasWeeklyAnomalies' => $hasWeeklyAnomalies,
            'hasMonthlyAnomalies' => $hasMonthlyAnomalies,
            'weeklyData' => $weeklyData,
            'attendances' => $attendances,
            'timeOffRequests' => $timeOffRequests,
        ];
    }

    /**
     * Calcola i giorni lavorativi in un periodo
     */
    private function calculateWorkingDays($dateFrom, $dateTo, $festiveDays): int
    {
        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        $workingDays = 0;

        $current = $start->copy();
        while ($current->lte($end)) {
            // Salta weekend (sabato e domenica)
            if (! $current->isWeekend()) {
                // Controlla se è un giorno festivo
                $dayMonth = $current->format('m-d');
                if (! in_array($dayMonth, $festiveDays)) {
                    $workingDays++;
                }
            }
            $current->addDay();
        }

        return $workingDays;
    }

    /**
     * Calcola le ore lavorative previste per un periodo escludendo weekend e festivi
     */
    private function calculateExpectedWorkingHours($dateFrom, $dateTo, $festiveDays): float
    {
        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        $workingDays = 0;

        $current = $start->copy();
        while ($current->lte($end)) {
            // Salta weekend (sabato e domenica)
            if (! $current->isWeekend()) {
                // Controlla se è un giorno festivo
                $dayMonth = $current->format('m-d');
                if (! in_array($dayMonth, $festiveDays)) {
                    $workingDays++;
                }
            }
            $current->addDay();
        }

        return $workingDays * 8; // 8 ore per giorno lavorativo
    }

    /**
     * Calcola le ore lavorate in una settimana specifica
     */
    private function calculateWeeklyHours($user, $weekStart, $weekEnd, $attendances, $timeOffRequests, $festiveDays): float
    {
        $weeklyHours = 0;

        // Ore da presenze
        $weekAttendances = $attendances->filter(function ($attendance) use ($weekStart, $weekEnd) {
            $attendanceDate = Carbon::parse($attendance->date);

            return $attendanceDate->between($weekStart, $weekEnd);
        });

        foreach ($weekAttendances as $attendance) {
            if ($attendance->time_in && $attendance->time_out) {
                $timeIn = Carbon::parse($attendance->time_in);
                $timeOut = Carbon::parse($attendance->time_out);
                $weeklyHours += $timeIn->diffInMinutes($timeOut) / 60;
            }
        }

        // Ore da permessi (ferie, rol, etc.)
        $weekTimeOffs = $timeOffRequests->filter(function ($timeOff) use ($weekStart, $weekEnd) {
            $timeOffStart = Carbon::parse($timeOff->date_from);
            $timeOffEnd = Carbon::parse($timeOff->date_to);

            return $timeOffStart->between($weekStart, $weekEnd) ||
                   $timeOffEnd->between($weekStart, $weekEnd) ||
                   ($timeOffStart->lte($weekStart) && $timeOffEnd->gte($weekEnd));
        });

        foreach ($weekTimeOffs as $timeOff) {
            $timeOffStart = Carbon::parse($timeOff->date_from);
            $timeOffEnd = Carbon::parse($timeOff->date_to);

            // Calcola solo la porzione nella settimana corrente
            $actualStart = $timeOffStart->lt($weekStart) ? $weekStart : $timeOffStart;
            $actualEnd = $timeOffEnd->gt($weekEnd) ? $weekEnd : $timeOffEnd;

            $weeklyHours += $actualStart->diffInMinutes($actualEnd) / 60;
        }

        return round($weeklyHours, 2);
    }

    /**
     * Calcola il totale delle ore effettive nel periodo
     */
    private function calculateTotalActualHours($attendances, $timeOffRequests): float
    {
        $totalHours = 0;

        // Ore da presenze
        foreach ($attendances as $attendance) {
            if ($attendance->time_in && $attendance->time_out) {
                $timeIn = Carbon::parse($attendance->time_in);
                $timeOut = Carbon::parse($attendance->time_out);
                $totalHours += $timeIn->diffInMinutes($timeOut) / 60;
            }
        }

        // Ore da permessi
        foreach ($timeOffRequests as $timeOff) {
            $timeOffStart = Carbon::parse($timeOff->date_from);
            $timeOffEnd = Carbon::parse($timeOff->date_to);
            $totalHours += $timeOffStart->diffInMinutes($timeOffEnd) / 60;
        }

        return round($totalHours, 2);
    }

    public function updateData(Request $request, User $user)
    {
        $request->validate([
            'title' => 'required|string|max:50',
            'cfp' => 'required|string|max:16',
            'birth_date' => 'required|date',
            'company_name' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:16',
            'mobile_number' => 'nullable|string|max:15',
            'phone_number' => 'nullable|string|max:15',
            'category' => 'required|string|max:50',
            'weekly_hours' => 'required|integer|min:0',
            'badge_code' => 'nullable|string|max:50',
            'employee_code' => 'nullable|string|max:50',
        ]);

        $user->update($request->all());

        return redirect()->route('users.edit', $user->id)->with('success', __('personnel.users_updated'));
    }

    public function updateDefaultSchedules(Request $request, User $user)
    {
        $validated = $request->validate([
            'schedule' => 'array',
            'schedule.*.day' => 'required|string|in:' . implode(',', UserDefaultSchedule::DAYS),
            'schedule.*.hour_start' => 'required|date_format:H:i',
            'schedule.*.hour_end' => 'required|date_format:H:i',
            'schedule.*.attendance_type_id' => 'required|exists:attendance_types,id',
        ]);

        $scheduleItems = $validated['schedule'] ?? [];

        if (empty($scheduleItems)) {
            $user->defaultSchedules()->delete();

            return redirect()->route('users.edit', $user)->with('success', 'Calendario aggiornato.');
        }

        $normalized = collect($scheduleItems)->map(function (array $item) {
            $start = Carbon::createFromFormat('H:i', $item['hour_start']);
            $end = Carbon::createFromFormat('H:i', $item['hour_end']);

            if ($end->lte($start)) {
                throw ValidationException::withMessages([
                    'hour_end' => 'L\'orario di fine deve essere successivo a quello di inizio.',
                ]);
            }

            return [
                'day' => $item['day'],
                'hour_start' => $start->format('H:i'),
                'hour_end' => $end->format('H:i'),
                'total_hours' => $start->diffInMinutes($end) / 60,
                'attendance_type_id' => $item['attendance_type_id'],
            ];
        });

        $user->defaultSchedules()->delete();
        $user->defaultSchedules()->createMany($normalized->toArray());

        return redirect()->route('users.edit', $user)->with('success', 'Calendario aggiornato.');
    }

    public function generateDefaultSchedules(User $user)
    {
        $user->ensureDefaultSchedule();

        return redirect()->route('users.default-schedules.calendar', $user)->with('success', 'Calendario standard generato.');
    }

    public function updateResidence(Request $request, User $user)
    {
        $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'street_number' => 'required|string|max:10',
            'postal_code' => 'required|string|max:10',
            'province' => 'required|string|max:50',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => __('personnel.users_updated'),
            'user' => $user,
        ]);
    }

    public function updateLocation(Request $request, User $user)
    {
        $request->validate([
            'location_address' => 'required|string|max:255',
            'location_city' => 'required|string|max:100',
            'location_street_number' => 'required|string|max:10',
            'location_postal_code' => 'required|string|max:10',
            'location_province' => 'required|string|max:50',
            'location_latitude' => 'required|numeric',
            'location_longitude' => 'required|numeric',
        ]);

        $user->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => __('personnel.users_updated'),
            'user' => $user,
        ]);
    }

    public function addVehicles(User $user)
    {

        $brands = Vehicle::select('brand')->distinct()->orderBy('brand', 'asc')->get();
        $brands = $brands->pluck('brand')->map(function ($brand) {
            return preg_replace('/[^a-zA-Z0-9\s]/', '', $brand);
        })->unique()->toArray();

        return view('admin.personnel.users.vehicles.create', [
            'user' => $user,
            'brands' => $brands,
            'vehicleTypes' => $this->vehicleTypes,
            'ownershipTypes' => $this->ownershipTypes,
            'purchaseTypes' => $this->purchaseTypes,
        ]);
    }

    public function associateVehicle(Request $request, User $user)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'vehicle_type' => 'required|integer',
            'plate_number' => 'nullable|string|max:20',
            'ownership_type' => 'required|integer',
            'purchase_type' => 'required|integer',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
            'mileage' => 'nullable|numeric',
            'mileage_update_date' => 'nullable|date',
        ]);

        $user->vehicles()->attach($request->vehicle_id, [
            'vehicle_type' => $request->vehicle_type,
            'plate_number' => $request->plate_number,
            'ownership_type' => $request->ownership_type,
            'purchase_type' => $request->purchase_type,
            'contract_start_date' => $request->contract_start_date,
            'contract_end_date' => $request->contract_end_date,
            'mileage' => $request->mileage,
            'mileage_update_date' => $request->mileage_update_date,
        ]);

        return redirect()->route('users.edit', $user)->with('success', __('personnel.users_vehicles_added'));
    }

    public function destroyUserVehicle(Request $request, User $user, Vehicle $vehicle)
    {
        $user->vehicles()->detach($vehicle->id);

        return redirect()->route('users.edit', $user)->with('success', __('personnel.users_vehicles_deleted'));
    }

    public function editUserVehicle(User $user, Vehicle $vehicle)
    {

        $joinedVehicle = $user->vehicles()->where('vehicles.id', $vehicle->id)->first();
        $mileageUpdates = $vehicle->mileageUpdates()->where('user_id', $user->id)->orderBy('update_date', 'desc')->get();

        return view('admin.personnel.users.vehicles.edit', [
            'user' => $user,
            'vehicle' => $vehicle,
            'joinedVehicle' => $joinedVehicle,
            'vehicleTypes' => $this->vehicleTypes,
            'ownershipTypes' => $this->ownershipTypes,
            'purchaseTypes' => $this->purchaseTypes,
            'mileageUpdates' => $mileageUpdates,
        ]);
    }

    public function updateUserVehicle(Request $request, User $user)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'vehicle_type' => 'required|integer',
            'plate_number' => 'nullable|string|max:20',
            'ownership_type' => 'required|integer',
            'purchase_type' => 'required|integer',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
            'mileage' => 'nullable|numeric',
            'mileage_update_date' => 'nullable|date',
        ]);

        $vehicle = Vehicle::find($request->vehicle_id);
        $pivot = $user->vehicles()->where('vehicles.id', $vehicle->id)->first();

        if ($pivot && isset($request->mileage) && $request->mileage !== null) {
            $previousMileage = $pivot->pivot->mileage;
            if ($previousMileage != $request->mileage) {
                $vehicle->mileageUpdates()->create([
                    'user_id' => $user->id,
                    'mileage' => $request->mileage,
                    'update_date' => $request->mileage_update_date ?? now(),
                ]);
            }
        }

        $user->vehicles()->updateExistingPivot($request->vehicle_id, [
            'vehicle_type' => $request->vehicle_type,
            'plate_number' => $request->plate_number,
            'ownership_type' => $request->ownership_type,
            'purchase_type' => $request->purchase_type,
            'contract_start_date' => $request->contract_start_date,
            'contract_end_date' => $request->contract_end_date,
            'mileage' => $request->mileage,
            'mileage_update_date' => $request->mileage_update_date,
        ]);

        return redirect()->route('users.edit', $user)->with('success', __('personnel.users_vehicles_updated'));
    }

    public function destroyUserCompany(Request $request, User $user, Company $company)
    {
        $user->companies()->detach($company->id);

        return redirect()->route('users.edit', $user)->with('success', __('personnel.users_company_deleted'));
    }

    public function destroyUserGroup(Request $request, User $user, Group $group)
    {
        $user->groups()->detach($group->id);

        return redirect()->route('users.edit', $user)->with('success', __('personnel.users_group_deleted'));
    }

    /**
     * Valida un indirizzo utilizzando l'API di Nominatim.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchAddress(Request $request)
    {

        // 2. Costruzione della query per Nominatim (utilizzando il parametro 'q')
        // Combiniamo i campi in una singola stringa indirizzo
        $fullAddress = $request['address'];

        // Parametri per la richiesta a Nominatim
        $queryParams = [
            'q' => $fullAddress,
            'format' => 'jsonv2', // Usiamo jsonv2 per un formato più moderno
            'addressdetails' => 1, // Chiediamo i dettagli dell'indirizzo nella risposta
            'limit' => 1, // Chiediamo solo il risultato migliore
        ];

        // 3. Invio della richiesta a Nominatim usando il client HTTP di Laravel
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'IFT/1.0', // Sostituisci con un nome significativo e la tua email
            ])->get('https://nominatim.openstreetmap.org/search', $queryParams);

            // Verifica se la richiesta ha avuto successo
            if ($response->successful()) {
                $data = $response->json();

                // 4. Analisi della risposta
                if (! empty($data)) {
                    // Nominatim ha trovato almeno un risultato
                    $firstResult = $data[0];

                    // Esempi di come accedere ai dati:
                    $latitude = $firstResult['lat'];
                    $longitude = $firstResult['lon'];
                    $displayName = $firstResult['display_name'];
                    $addressDetails = $firstResult['address']; // Array con i dettagli dell'indirizzo

                    // Puoi fare ulteriori controlli qui per "validare" l'indirizzo
                    // ad esempio, confrontare i dettagli restituiti con quelli inseriti dall'utente.
                    // Ricorda che questa è una validazione basata su OSM, non postale ufficiale.

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Indirizzo trovato.',
                        'content' => [
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'display_name' => $displayName,
                            'address_details' => $addressDetails,
                            // Puoi aggiungere altri dati dal risultato di Nominatim se necessario
                        ],
                    ]);
                } else {
                    // Nessun risultato trovato per l'indirizzo
                    return response()->json([
                        'status' => 'not_found',
                        'message' => 'Indirizzo non trovato.',
                        'data' => null,
                    ], 404); // Codice di stato 404 Not Found
                }
            } else {
                // La richiesta HTTP a Nominatim non è andata a buon fine
                // Puoi loggare l'errore o restituire un messaggio generico
                Log::error('Nominatim API request failed: '.$response->status());

                return response()->json([
                    'status' => 'error',
                    'message' => 'Errore durante la comunicazione con il servizio di validazione indirizzi.',
                    'details' => $response->body(), // Potrebbe contenere informazioni sull'errore da Nominatim
                ], $response->status()); // Usa il codice di stato della risposta di Nominatim

            }
        } catch (\Exception $e) {
            // Gestione di eventuali eccezioni durante la richiesta
            Log::error('Exception during Nominatim API call: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Si è verificato un errore imprevisto.',
                'details' => $e->getMessage(),
            ], 500); // Codice di stato 500 Internal Server Error
        }
    }

    private function getFestiveDays()
    {

        $currentDate = \Carbon\Carbon::today();

        $easterDay = $this->calculateEasterDay($currentDate->year);
        $easterDay = $easterDay ? $easterDay->format('m-d') : null;
        $easterMonday = $this->calculateEasterMonday($currentDate->year)->format('m-d');

        // List of known vacation days
        $knownHolidays = [
            '01-01', // New Year's Day
            '12-25', // Christmas
            '12-26', // Boxing Day
            '01-06', // Epiphany
            '04-25', // Liberation Day
            '05-01', // Labor Day
            '06-02', // Republic Day
            '08-15', // Assumption Day
            '11-01', // All Saints' Day
            '11-02', // All Souls' Day
            '12-08', // Immaculate Conception
            $easterDay, // Easter
            $easterMonday, // Easter Monday
        ];

        return $knownHolidays;
    }

    private function calculateEasterDay($year)
    {
        $a = $year % 19;
        $b = (int) ($year / 100);
        $c = $year % 100;
        $d = (int) ($b / 4);
        $e = $b % 4;
        $f = (int) (($b + 8) / 25);
        $g = (int) (($b - $f + 1) / 16);
        $h = (int) ((19 * $a + $b - $d - $g + 15) % 30);
        $i = (int) ($c / 16);
        $k = (int) ($c % 16);
        $l = (int) ((32 + 2 * $e + 2 * $i - $h - $k) % 7);
        $m = (int) (($a + 11 * $h + 22 * $l) / 451);

        return \Carbon\Carbon::createFromDate($year, (int) (($h + $l - 7 * $m + 114) / 31), ($h + $l - 7 * $m + 114) % 31 + 1);
    }

    private function calculateEasterMonday($year)
    {
        $easterDay = $this->calculateEasterDay($year);

        return $easterDay->modify('+1 day');
    }
}
