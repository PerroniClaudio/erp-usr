<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
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


        return view('admin.personnel.users.vehicles.edit', [
            'user' => $user,
            'vehicle' => $vehicle,
            'joinedVehicle' => $joinedVehicle,
            'vehicleTypes' => $this->vehicleTypes,
            'ownershipTypes' => $this->ownershipTypes,
            'purchaseTypes' => $this->purchaseTypes,
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
