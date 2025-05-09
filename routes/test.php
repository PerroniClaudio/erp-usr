<?php

use App\Models\Attendance;
use App\Models\AttendanceType;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\TimeOffRequest;
use App\Models\TimeOffType;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;

Route::get('/test-time-off', function () {

    return;

    DB::transaction(function () {
        $type = TimeOffType::create([
            'name' => 'Test Time Off Type',
        ]);

        for ($i = 0; $i < 3; $i++) {

            $newUser = User::create([
                'name' => 'Test User ' . $i,
                'email' => 'testuser' . $i . '@example.com',
                'password' => bcrypt('password'),
            ]);

            for ($j = 1; $j <= 3; $j++) {

                $start_date = fake()->dateTimeBetween('now', '+1 month');
                $end_date = (clone $start_date)->modify('+' . rand(1, 5) . ' days');

                TimeOffRequest::create([
                    'user_id' => $newUser->id,
                    'company_id' => 1,
                    'time_off_type_id' => 1,
                    'status' => 2,
                    'date_from' => $start_date,
                    'date_to' => $end_date,
                    'batch_id' => $i
                ]);
            }
        }
    });
});

Route::get('/test-legacy-db', function () {

    return;

    $data = DB::connection('legacy_mysql')->table('presenze')
        ->join('combo_personale_tipologia', 'presenze.cptid', '=', 'combo_personale_tipologia.cptid')
        ->where('peid', 22)
        ->whereBetween(DB::raw("STR_TO_DATE(data_inizio, '%d-%m-%Y')"), ['2025-04-01', '2025-05-01'])
        ->orderBy(DB::raw("STR_TO_DATE(data_inizio, '%d-%m-%Y')"), 'ASC')
        ->select('voce', 'presenze.data_inizio', 'presenze.ora_inizio', 'presenze.ora_fine')
        ->get();

    $ferie = [];

    foreach ($data as $row) {

        $attendance_type = AttendanceType::where('name', $row->voce)->first();

        if (!$attendance_type) {

            // Giorno di ferie o rol

            $timeOffType = TimeOffType::where('name', $row->voce)->first();

            $date_from = $row->data_inizio . ' ' . $row->ora_inizio;
            $date_to = $row->data_inizio . ' ' . $row->ora_fine;

            $ferie[] = [
                'user_id' => 1,
                'company_id' => 1,
                'time_off_type_id' => $timeOffType->id,
                'status' => 2,
                'date_from' => \Carbon\Carbon::createFromFormat('d-m-Y H:i', $date_from)->format('Y-m-d H:i'),
                'date_to' => \Carbon\Carbon::createFromFormat('d-m-Y H:i', $date_to)->format('Y-m-d H:i'),
            ];
        } else {

            // Presenza


            Attendance::create([
                'user_id' => 1,
                'company_id' => 1,
                'date' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data_inizio)->format('Y-m-d'),
                'time_in' => $row->ora_inizio,
                'time_out' => $row->ora_fine,
                'hours' => (strtotime($row->ora_fine) - strtotime($row->ora_inizio)) / 3600,
                'attendance_type_id' => $attendance_type->id,
            ]);
        }
    }

    $groupedFerie = [];

    foreach ($ferie as $ferieItem) {
        $dateFrom = \Carbon\Carbon::parse($ferieItem['date_from'])->format('Y-m-d');
        if (!isset($groupedFerie[$dateFrom])) {
            $groupedFerie[$dateFrom] = [];
        }
        $groupedFerie[$dateFrom][] = $ferieItem;
    }

    $formattedFerie = [];

    foreach ($groupedFerie as $date => $items) {
        $batchId = uniqid();
        foreach ($items as $item) {
            $item['batch_id'] = $batchId;

            $formattedFerie[] = $item;
        }
    }

    foreach ($formattedFerie as $ferieItem) {
        TimeOffRequest::create($ferieItem);
    }
});

Route::get('/test-cedolino', function () {

    return;

    $mese = 'Aprile';
    $anno = 2025;
    $user = User::find(1);

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
        'Dicembre' => 12
    ];

    $meseNumero = $mesiMap[$mese];
    $primoGiorno = Carbon::createFromDate($anno, $meseNumero, 1)->startOfDay();
    $ultimoGiorno = Carbon::createFromDate($anno, $meseNumero, 1)->endOfMonth()->endOfDay();

    // Generiamo il PDF dalla vista
    $pdf = PDF::loadView('cedolini.pdf', [
        'user' => $user,
        'mese' => $mese,
        'anno' => $anno,
        'meseNumero' => $meseNumero,
        'primoGiorno' => $primoGiorno,
        'ultimoGiorno' => $ultimoGiorno
    ]);

    // Impostiamo le opzioni del PDF
    $pdf->setPaper('a4', 'landscape');
    $pdf->setOptions([
        'dpi' => 150,
        'defaultFont' => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true
    ]);

    return $pdf->download('cedolino_' . $user->name . '_' . $mese . '_' . $anno . '.pdf');
});
