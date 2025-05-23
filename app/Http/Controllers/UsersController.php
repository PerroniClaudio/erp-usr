<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\TimeOffRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller {
    //

    private $vehicleTypes;
    private $ownershipTypes;
    private $purchaseTypes;

    public function __construct() {
        $this->vehicleTypes = collect([
            (object)['id' => 1, 'name' => 'Autoveicolo'],
            (object)['id' => 2, 'name' => 'Ciclomotore'],
            (object)['id' => 3, 'name' => 'Autocarro']
        ]);

        $this->ownershipTypes = collect([
            (object)['id' => 1, 'name' => 'Aziendale'],
            (object)['id' => 2, 'name' => 'Personale']
        ]);

        $this->purchaseTypes = collect([
            (object)['id' => 1, 'name' => 'Acquisto'],
            (object)['id' => 2, 'name' => 'Leasing'],
            (object)['id' => 3, 'name' => 'Noleggio a breve termine'],
            (object)['id' => 4, 'name' => 'Noleggio a lungo termine']
        ]);
    }

    public function index() {
        $users = User::all();
        return view('admin.personnel.users.index', compact('users'));
    }

    public function edit(User $user) {
        return view('admin.personnel.users.edit', compact('user'));
    }

    public function exportPdf(User $user, Request $request) {
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
    }

    public function exportPresenzePdf(User $user, Request $request) {
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
            'Dicembre' => 12
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
            return $presenza->tipologia == 'Lavoro in sede' && !$presenza->annullata;
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
            'totale_pagine' => ceil($presenze->count() / 30) // Assumiamo circa 30 righe per pagina
        ]);

        // Impostiamo le opzioni del PDF
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);

        return $pdf->download('presenze_' . $user->name . '_' . $mese . '_' . $anno . '.pdf');
    }

    private function preparePresenzeData($attendances, $timeOffRequests, $user, $primoGiorno, $ultimoGiorno) {
        $presenze = new Collection();


        // Aggiungiamo le presenze
        foreach ($attendances as $attendance) {
            $data = Carbon::parse($attendance->date);
            $giorno_settimana = $this->getGiornoSettimanaItaliano($data->format('D'));

            $tipologia = $attendance->attendanceType->name;

            $presenze->push((object)[
                'id' => $attendance->id,
                'persona' => $user->name,
                'azienda' => $attendance->company->name ?? 'IFORTECH S.R.L.',
                'tipologia' => $tipologia,
                'data' => $data,
                'data_formattata' => $data->format('d-m-Y') . ' ' . $giorno_settimana,
                'ora_inizio' => Carbon::parse($attendance->time_in)->format('H:i'),
                'ora_fine' => Carbon::parse($attendance->time_out)->format('H:i'),
                'ore' => number_format(Carbon::parse($attendance->time_in)->diffInMinutes(Carbon::parse($attendance->time_out)) / 60, 2),
                'annullata' => false,
                'giornata' => null
            ]);
        }

        // Aggiungiamo le ferie e i permessi
        foreach ($timeOffRequests as $request) {
            $presenze->push((object)[
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
                'giornata' => null
            ]);
        }

        // Ordiniamo le presenze per data e ora
        return $presenze->sortBy([
            ['data', 'asc'],
            ['ora_inizio', 'asc']
        ]);
    }

    private function calcolaRiepilogo($presenze, $permessi) {
        $riepilogo = [
            'lavorato' => ['ore' => 0, 'giorni' => 0],
            'straordinario' => ['ore' => 0, 'giorni' => 0],
            'straordinario_notturno' => ['ore' => 0, 'giorni' => 0],
            'straordinario_festivo' => ['ore' => 0, 'giorni' => 0],
            'ferie' => ['ore' => 0, 'giorni' => 0],
            'corso_intra' => ['ore' => 0, 'giorni' => 0],
            'corso_extra' => ['ore' => 0, 'giorni' => 0],
            'rol' => ['ore' => 0, 'giorni' => 0]
        ];

        foreach ($presenze as $presenza) {

            switch ($presenza->attendanceType->name) {
                case 'Lavoro in sede':
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

    private function getGiornoSettimanaItaliano($giornoEn) {
        $giorni = [
            'Mon' => 'Lun',
            'Tue' => 'Mar',
            'Wed' => 'Mer',
            'Thu' => 'Gio',
            'Fri' => 'Ven',
            'Sat' => 'Sab',
            'Sun' => 'Dom'
        ];

        return $giorni[$giornoEn] ?? $giornoEn;
    }


    public function updateData(Request $request, User $user) {
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

    public function updateResidence(Request $request, User $user) {
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
            'user' => $user
        ]);
    }

    public function updateLocation(Request $request, User $user) {
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
            'user' => $user
        ]);
    }

    public function addVehicles(User $user) {

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

    public function associateVehicle(Request $request, User $user) {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'vehicle_type' => 'required|integer',
            'plate_number' => 'nullable|string|max:20',
            'ownership_type' => 'required|integer',
            'purchase_type' => 'required|integer',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
            'mileage' => 'nullable|numeric',
            'mileage_update_date' => 'nullable|date'
        ]);

        $user->vehicles()->attach($request->vehicle_id, [
            'vehicle_type' => $request->vehicle_type,
            'plate_number' => $request->plate_number,
            'ownership_type' => $request->ownership_type,
            'purchase_type' => $request->purchase_type,
            'contract_start_date' => $request->contract_start_date,
            'contract_end_date' => $request->contract_end_date,
            'mileage' => $request->mileage,
            'mileage_update_date' => $request->mileage_update_date
        ]);

        return redirect()->route('users.edit', $user)->with('success', __('personnel.users_vehicles_added'));
    }

    public function destroyUserVehicle(Request $request, User $user, Vehicle $vehicle) {
        $user->vehicles()->detach($vehicle->id);

        return redirect()->route('users.edit', $user)->with('success', __('personnel.users_vehicles_deleted'));
    }

    public function editUserVehicle(User $user, Vehicle $vehicle) {

        $joinedVehicle = $user->vehicles()->where('vehicles.id', $vehicle->id)->first();
        $mileageUpdates = $vehicle->mileageUpdates()->where('user_id', $user->id)->orderBy('update_date', 'desc')->get();


        return view('admin.personnel.users.vehicles.edit', [
            'user' => $user,
            'vehicle' => $vehicle,
            'joinedVehicle' => $joinedVehicle,
            'vehicleTypes' => $this->vehicleTypes,
            'ownershipTypes' => $this->ownershipTypes,
            'purchaseTypes' => $this->purchaseTypes,
            'mileageUpdates' => $mileageUpdates
        ]);
    }

    public function updateUserVehicle(Request $request, User $user) {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'vehicle_type' => 'required|integer',
            'plate_number' => 'nullable|string|max:20',
            'ownership_type' => 'required|integer',
            'purchase_type' => 'required|integer',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
            'mileage' => 'nullable|numeric',
            'mileage_update_date' => 'nullable|date'
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
            'mileage_update_date' => $request->mileage_update_date
        ]);

        return redirect()->route('users.edit', $user)->with('success', __('personnel.users_vehicles_updated'));
    }

    public function destroyUserCompany(Request $request, User $user, Company $company) {
        $user->companies()->detach($company->id);

        return redirect()->route('users.edit', $user)->with('success', __('personnel.users_company_deleted'));
    }

    public function destroyUserGroup(Request $request, User $user, Group $group) {
        $user->groups()->detach($group->id);

        return redirect()->route('users.edit', $user)->with('success', __('personnel.users_group_deleted'));
    }



    /**
     * Valida un indirizzo utilizzando l'API di Nominatim.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchAddress(Request $request) {

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
                'User-Agent' => 'IFT/1.0' // Sostituisci con un nome significativo e la tua email
            ])->get('https://nominatim.openstreetmap.org/search', $queryParams);

            // Verifica se la richiesta ha avuto successo
            if ($response->successful()) {
                $data = $response->json();

                // 4. Analisi della risposta
                if (!empty($data)) {
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
                        ]
                    ]);
                } else {
                    // Nessun risultato trovato per l'indirizzo
                    return response()->json([
                        'status' => 'not_found',
                        'message' => 'Indirizzo non trovato.',
                        'data' => null
                    ], 404); // Codice di stato 404 Not Found
                }
            } else {
                // La richiesta HTTP a Nominatim non è andata a buon fine
                // Puoi loggare l'errore o restituire un messaggio generico
                Log::error('Nominatim API request failed: ' . $response->status());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Errore durante la comunicazione con il servizio di validazione indirizzi.',
                    'details' => $response->body() // Potrebbe contenere informazioni sull'errore da Nominatim
                ], $response->status()); // Usa il codice di stato della risposta di Nominatim

            }
        } catch (\Exception $e) {
            // Gestione di eventuali eccezioni durante la richiesta
            Log::error('Exception during Nominatim API call: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Si è verificato un errore imprevisto.',
                'details' => $e->getMessage()
            ], 500); // Codice di stato 500 Internal Server Error
        }
    }
}
