<?php

use App\Models\Attendance;
use App\Models\AttendanceType;
use App\Models\BusinessTrip;
use App\Models\BusinessTripTransfer;
use App\Models\Company;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\TimeOffRequest;
use App\Models\TimeOffType;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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

Route::get('/test-trasferte', function () {

    return;

    DB::transaction(function () {
        $peid = 1;
        $userId = 2;
        $startDate = "2025-01-01";
        $endDate = "2025-05-31";

        $user_vehicle = User::find($userId)->vehicles->first();

        $data = DB::connection('legacy_mysql')->table('trasferte_usr')
            ->join('trasferte_usr_itinerari', 'trasferte_usr.trpid', '=', 'trasferte_usr_itinerari.trpid')
            ->where('trasferte_usr.peid', $peid)
            ->whereBetween(DB::raw("STR_TO_DATE(trasferte_usr.data_inizio, '%d-%m-%Y')"), [$startDate, $endDate])
            ->orderBy(DB::raw("STR_TO_DATE(trasferte_usr.data_inizio, '%d-%m-%Y')"), 'ASC')
            ->select('trasferte_usr.*', 'trasferte_usr_itinerari.*')
            ->get();

        $trasferte = [];

        foreach ($data as $row) {
            // Raggruppa gli itinerari per nome
            if (!isset($trasferte[$row->nome])) {

                $coordinate_da = $row->coordda ? explode(',', $row->coordda) : null;

                if ($coordinate_da === null) {
                    Log::error("Coordinate not found for transfer: " . $row->nome . " on date: " . $row->data);
                    continue;
                }

                $trasferte[$row->nome] = [
                    'user_id' => $userId,
                    'date_from' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data_inizio)->format('Y-m-d'),
                    'date_to' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data_fine)->format('Y-m-d'),
                    'status' => $row->incorso,
                    'code' => $row->nome,
                    'trasferimenti' => [
                        [
                            'date' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data)->format('Y-m-d H:i:s'),
                            'address' => 'N/A',
                            'province' => 'N/A',
                            'city' => $row->da,
                            'zip_code' => 'N/A',
                            'latitude' => $coordinate_da ? $coordinate_da[0] : null,
                            'longitude' => $coordinate_da ? $coordinate_da[1] : null,
                        ]
                    ],
                ];
            }

            $coordinate = $row->coorda ? explode(',', $row->coorda) : null;

            if ($coordinate === null) {
                Log::error("Coordinate not found for transfer: " . $row->nome . " on date: " . $row->data);
                continue;
            }

            $trasferte[$row->nome]['trasferimenti'][] = [
                'date' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data)->format('Y-m-d H:i:s'),
                'address' => 'N/A',
                'province' => 'N/A',
                'city' => $row->a,
                'zip_code' => 'N/A',
                'latitude' => $coordinate ? $coordinate[0] : null,
                'longitude' => $coordinate ? $coordinate[1] : null,
            ];
        }

        foreach ($trasferte as $code => $trasferta) {

            $businessTrip = BusinessTrip::create([
                'company_id' => 1,
                'user_id' => $trasferta['user_id'],
                'date_from' => $trasferta['date_from'],
                'date_to' => $trasferta['date_to'],
                'status' => $trasferta['status'],
                'expense_type' => 1,
                'code' => $code,
            ]);

            foreach ($trasferta['trasferimenti'] as $transfer) {
                $businessTrip->transfers()->create([
                    'business_trip_id' => $businessTrip->id,
                    'company_id' => 1,
                    'date' => $transfer['date'],
                    'address' => $transfer['address'],
                    'city' => $transfer['city'],
                    'province' => $transfer['province'],
                    'zip_code' => $transfer['zip_code'],
                    'latitude' => $transfer['latitude'],
                    'longitude' => $transfer['longitude'],
                    'vehicle_id' => $user_vehicle ? $user_vehicle->id : null,
                ]);
            }
        }
    });
});

Route::get('/test-legacy-db', function () {
    return;

    echo "Starting legacy DB import...\n";

    $peid = 13;
    $userId = 8;
    $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', '01-01-2025');
    $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', '31-05-2025');
    $cachedCompanies = [];
    $companyCache = [];

    $data = DB::connection('legacy_mysql')->table('presenze')
        ->join('combo_personale_tipologia', 'presenze.cptid', '=', 'combo_personale_tipologia.cptid')
        ->where('peid', $peid)
        ->whereBetween(DB::raw("STR_TO_DATE(data_inizio, '%d-%m-%Y')"), [$startDate, $endDate])
        ->orderBy(DB::raw("STR_TO_DATE(data_inizio, '%d-%m-%Y')"), 'ASC')
        ->select('voce', 'presenze.data_inizio', 'presenze.ora_inizio', 'presenze.ora_fine', 'presenze.aid')
        ->get();

    $ferie = [];

    foreach ($data as $row) {
        Log::info('Processing attendance record', [
            'aid' => $row->aid,
            'voce' => $row->voce,
            'data_inizio' => $row->data_inizio,
            'ora_inizio' => $row->ora_inizio,
            'ora_fine' => $row->ora_fine,
        ]);

        if (!in_array($row->aid, $cachedCompanies)) {
            $cachedCompanies[] = $row->aid;


            $azienda = DB::connection('legacy_mysql')->table('aziende')
                ->where('aid', $row->aid ?? null)
                ->first();

            if (!$azienda) {
                Log::error("Azienda with aid " . ($row->aid ?? 'N/A') . " not found.");
                continue;
            }

            if ($azienda->name == 'IFORTECH S.R.L.') {

                $company = Company::find(1)->get();
            } else {
                $company = Company::where('name', $azienda->name)->first();
            }


            if (!$company) {
                $company = Company::create([
                    'name' => $azienda->name
                ]);
            }

            $companyCache[$row->aid] = $company;
        } else {
            $company = $companyCache[$row->aid];
        }

        $attendance_type = AttendanceType::where('name', $row->voce)->first();

        if (!$attendance_type) {
            // Giorno di ferie o rol
            $timeOffType = TimeOffType::where('name', $row->voce)->first();

            if (!$timeOffType) {
                continue;
            }

            $date_from = $row->data_inizio . ' ' . $row->ora_inizio;
            $date_to = $row->data_inizio . ' ' . $row->ora_fine;

            try {
                $fromCarbon = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $date_from);
                $toCarbon = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $date_to);
                $fromErrors = \Carbon\Carbon::getLastErrors();
                $toErrors = \Carbon\Carbon::getLastErrors();
                if (
                    $fromErrors['error_count'] > 0 || $fromErrors['warning_count'] > 0 ||
                    $toErrors['error_count'] > 0 || $toErrors['warning_count'] > 0
                ) {
                    continue; // Skip if date format is invalid
                }
            } catch (\Exception $e) {
                continue; // Skip if date format is invalid
            }

            $ferie[] = [
                'user_id' => $userId,
                'company_id' => 1,
                'time_off_type_id' => $timeOffType->id,
                'status' => 2,
                'date_from' => \Carbon\Carbon::createFromFormat('d-m-Y H:i', $date_from)->format('Y-m-d H:i'),
                'date_to' => \Carbon\Carbon::createFromFormat('d-m-Y H:i', $date_to)->format('Y-m-d H:i'),
            ];
        } else {
            // Presenza



            Attendance::create([
                'user_id' => $userId,
                'company_id' => 1,
                'date' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data_inizio)->format('Y-m-d'),
                'time_in' => $row->ora_inizio,
                'time_out' => $row->ora_fine,
                'hours' => (strtotime($row->ora_fine) - strtotime($row->ora_inizio)) / 3600,
                'attendance_type_id' => $attendance_type->id,
            ]);
        }
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

Route::get('/test-autoveicoli', function () {

    return;

    $files = [
        "Autocaravan.xlsx",
        "Benzina-IN.xlsx",
        "Elettrico-IN.xlsx",
        "Gasolio-IN.xlsx",
        "GPL-IN.xlsx",
        "Ibr-Benzina-IN.xlsx",
        "Ibr-Gasolio-IN.xlsx",
        "Plug-in-IN.xlsx",
        "Benzina-OUT.xlsx",
        "Elettrico-OUT.xlsx",
        "Gasolio-OUT.xlsx",
        "GPL-Metano-OUT.xlsx",
        "Ibr-Benzina-OUT.xlsx",
        "Ibr-Gasolio-OUT.xlsx",
        "Plug-in-OUT.xlsx",
        "Motoveicoli.xlsx",
    ];

    $url_prefix = "https://aci.gov.it/app/uploads/2024/12/";



    foreach ($files as $file) {

        $data = [];

        if ($file == "Gasolio-IN.xlsx") {
            $url = "https://aci.gov.it/app/uploads/2025/01/Gasolio-IN.xlsx";
        } else {
            $url = $url_prefix . $file;
        }
        // Check if the file already exists in storage
        if (!Storage::exists($file)) {
            // Download the file from the URL
            $fileContents = file_get_contents($url);
            Storage::put($file, $fileContents);
        }

        $filePath = storage_path('app/private/' . $file);

        $data = Excel::toArray([], $filePath);

        echo $file;
    }
});

Route::get('/test-reverse-address', function () {

    return;

    $businessTrip = BusinessTrip::where('code', '2017TRA7185')->first();

    foreach ($businessTrip->transfers as $transfer) {

        $latitude = $transfer->latitude;
        $longitude = $transfer->longitude;

        if ($latitude && $longitude) {
            $response = Http::withHeaders([
                'User-Agent' => 'IFT/1.0' // Sostituisci con un nome significativo e la tua email
            ])->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
                'addressdetails' => 1,
            ]);



            if ($response->successful()) {
                $data = $response->json();

                $address = $data['address']['road'];
                $province = $data['address']['county'];
                $zip_code = $data['address']['postcode'];

                Log::info("Address for transfer on {$transfer->date}: " . json_encode($data));
            } else {
                Log::error("Failed to fetch address for transfer on {$transfer->date}");
            }
        } else {
            Log::warning("Transfer on {$transfer->date} has no coordinates.");
        }
    }
});

Route::get('/test-email', function () {

    $user = User::find(2);
    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\FailedAttendance());


    // $user = User::find(2);

    // // Invia un'email di prova
    // \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\TimeOffRequest("683560a66c7a5", $user, false));

    // echo "Email inviata a " . $user->email;
});
