<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
use App\Models\BusinessTripExpense;
use App\Models\BusinessTripTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Storage;

class BusinessTripController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        $user = $request->user();
        $businessTrips = BusinessTrip::where('user_id', $user->id)
            ->with(['user'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('standard.business_trips.index', [
            'businessTrips' => $businessTrips,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        $companies = Auth::user()->companies;

        return view('standard.business_trips.create', [
            'companies' => $companies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //

        $fields = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $code = date('Y') . 'TRA' . str_pad(BusinessTrip::count() + 1, 5, '0', STR_PAD_LEFT);

        $user = $request->user();

        $businessTrip = BusinessTrip::create([
            'user_id' => $user->id,
            'date_from' => $fields['date_from'],
            'date_to' => $fields['date_to'],
            'status' => $request->status,
            'company_id' => $request->company_id,
            'code' => $code,
            'expense_type' => 0,
        ]);

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Trasferta creata con successo');
    }

    /**
     * Display the specified resource.
     */
    public function show(BusinessTrip $businessTrip) {
        //

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusinessTrip $businessTrip) {
        //

        $companies = Auth::user()->companies;
        $raw_transfers = BusinessTripTransfer::where('business_trip_id', $businessTrip->id)->with(['company'])->get();

        $has_na_address = $raw_transfers->contains(function ($transfer) {
            return $transfer->address === 'N/A';
        });

        if ($has_na_address) {
            $transfers = $this->cleanupTransfers(
                $raw_transfers
            );
        } else {
            $transfers = $raw_transfers;
        }


        return view('standard.business_trips.edit', [
            'businessTrip' => $businessTrip,
            'companies' => $companies,
            'expenses' => BusinessTripExpense::where('business_trip_id', $businessTrip->id)->with(['company'])->get(),
            'transfers' => $transfers,
        ]);
    }

    private function cleanupTransfers($transfers) {

        foreach ($transfers as $transfer) {
            if ($transfer->address == "N/A") {

                $address_details = $this->resolveAddressFromCoordinates(
                    $transfer->latitude,
                    $transfer->longitude
                );
                $transfer->address = $address_details['address'];
                $transfer->city = $address_details['city'];
                $transfer->province = $address_details['province'];
                $transfer->zip_code = $address_details['zip_code'];
                $transfer->save();
            }
        }

        return $transfers;
    }


    private function resolveAddressFromCoordinates($latitude, $longitude) {
        // Utilizza l'API di Nominatim per risolvere l'indirizzo
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


            return [
                'address' => $data['address']['road'] ?? 'N/A',
                'city' => $data['address']['city'] ?? $data['address']['town'] ?? $data['address']['village'] ?? 'N/A',
                'province' => $data['address']['county'] ?? 'N/A',
                'zip_code' => $data['address']['postcode'] ?? 'N/A',
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        } else {
            return [
                'address' => null,
                'city' => null,
                'province' => null,
                'zip_code' => null,
            ];
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessTrip $businessTrip) {
        //

        $fields = $request->validate([
            'date_from' => 'required|string',
            'date_to' => 'required|string',
            'status' => 'required|integer',
        ]);

        $businessTrip->update($fields);

        return back()->with('success', 'Trasferta aggiornata con successo');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessTrip $businessTrip) {
        //

        $businessTrip->update([
            'status' => 2,
        ]);

        return back()->with('success', 'Trasferta eliminata con successo');
    }

    /**
     * * SPESE *
     */

    public function getExpenses(BusinessTrip $businessTrip) {
        $expenses = BusinessTripExpense::where('business_trip_id', $businessTrip->id)->with(['company'])->get();

        return response([
            'expenses' => $expenses,
        ], 200);
    }

    public function createExpense(BusinessTrip $businessTrip) {
        $companies = Auth::user()->companies;

        return view('standard.business_trips.expenses.create', [
            'businessTrip' => $businessTrip,
            'companies' => $companies,
        ]);
    }

    public function editExpense(BusinessTrip $businessTrip, BusinessTripExpense $expense) {
        $companies = Auth::user()->companies;

        return view('standard.business_trips.expenses.edit', [
            'businessTrip' => $businessTrip,
            'expense' => $expense,
            'companies' => $companies,
        ]);
    }

    public function updateExpense(BusinessTrip $businessTrip, BusinessTripExpense $expense, Request $request) {

        $fields = $request->validate([
            'company_id' => 'required|integer',
            'payment_type' => 'required|string',
            'expense_type' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'zip_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $expense->update($fields);

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Spesa aggiornata con successo');
    }

    public function storeExpense(BusinessTrip $businessTrip, Request $request) {

        $fields = $request->validate([
            'company_id' => 'required|integer',
            'payment_type' => 'required|string',
            'expense_type' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'zip_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $expense = BusinessTripExpense::create([
            'business_trip_id' => $businessTrip->id,
            'company_id' => $request->company_id,
            'payment_type' => $request->payment_type,
            'expense_type' => $request->expense_type,
            'amount' => $request->amount,
            'date' => $request->date,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'zip_code' => $request->zip_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Spesa aggiunta con successo');
    }

    public function destroyExpense(BusinessTrip $businessTrip, BusinessTripExpense $expense) {
        //

        $expense->delete();

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Spesa eliminata con successo');
    }

    /**
     * * TRASFERIMENTI *
     */

    public function createTransfer(BusinessTrip $businessTrip) {
        $companies = Auth::user()->companies;
        $userVehicles = Auth::user()->vehicles;
        $userVehicles = $userVehicles->map(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'name' => $vehicle->brand . ' ' . $vehicle->model . ' - ' . $vehicle->pivot->plate_number,
            ];
        });


        return view('standard.business_trips.transfers.create', [
            'businessTrip' => $businessTrip,
            'companies' => $companies,
            'userVehicles' => $userVehicles,
        ]);
    }

    public function editTransfer(BusinessTrip $businessTrip, BusinessTripTransfer $transfer) {
        $companies = Auth::user()->companies;
        $userVehicles = Auth::user()->vehicles;
        $userVehicles = $userVehicles->map(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'name' => $vehicle->brand . ' ' . $vehicle->model . ' - ' . $vehicle->pivot->plate_number,
            ];
        });

        return view('standard.business_trips.transfers.edit', [
            'businessTrip' => $businessTrip,
            'transfer' => $transfer,
            'companies' => $companies,
            'userVehicles' => $userVehicles,
        ]);
    }

    public function getTransfers(BusinessTrip $businessTrip) {
        $transfers = BusinessTripTransfer::where('business_trip_id', $businessTrip->id)->with(['company'])->get();

        return response([
            'transfers' => $transfers,
        ], 200);
    }

    public function storeTransfer(BusinessTrip $businessTrip, Request $request) {

        $fields = $request->validate([
            'company_id' => 'required|integer',
            'date' => 'required|date',
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'zip_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'vehicle_id' => 'nullable|integer',
        ]);

        $transfer = BusinessTripTransfer::create([
            'business_trip_id' => $businessTrip->id,
            'company_id' => $request->company_id,
            'date' => $request->date,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'zip_code' => $request->zip_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'vehicle_id' => $request->vehicle_id,
        ]);

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Trasferimento aggiunto con successo');
    }

    public function updateTransfer(BusinessTrip $businessTrip, BusinessTripTransfer $transfer, Request $request) {

        $fields = $request->validate([
            'company_id' => 'required|integer',
            'date' => 'required|date',
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'zip_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'vehicle_id' => 'nullable|integer',
        ]);

        $transfer->update($fields);

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Trasferimento aggiornato con successo');
    }

    public function destroyTransfer(BusinessTrip $businessTrip, BusinessTripTransfer $transfer) {

        $transfer->delete();

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Trasferimento eliminato con successo');
    }

    public function generatePdf(BusinessTrip $businessTrip) {

        $transfers = BusinessTripTransfer::where('business_trip_id', $businessTrip->id)->with(['company'])->get();
        $vehicle_id = $transfers[0]->vehicle_id;
        $user_vehicles = Auth::user()->vehicles;

        $user_vehicle = null;
        foreach ($user_vehicles as $vehicle) {
            if ($vehicle->id == $vehicle_id) {
                $user_vehicle = $vehicle;
                break;
            }
        }

        $transferPairs = $this->generateTransfers($transfers);

        $pdf = PDF::loadView('cedolini.business_trips_pdf', [
            'businessTrip' => $businessTrip,
            'expenses' => BusinessTripExpense::where('business_trip_id', $businessTrip->id)->with(['company'])->get(),
            'transfers' => $transferPairs,
            'document_date' => date('Y-m-d'),
            'user_vehicle' => $user_vehicle,
        ]);

        return $pdf->download('trasferta_' . $businessTrip->code . '.pdf');
    }

    public function generateMonthlyPdf(Request $request) {
        $fields = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900',
        ]);

        $start_of_month = date('Y-m-d', strtotime($fields['year'] . '-' . $fields['month'] . '-01'));
        $end_of_month = date('Y-m-t', strtotime($fields['year'] . '-' . $fields['month'] . '-01'));

        $user = $request->user();

        $businessTrips = BusinessTrip::where('user_id', $user->id)
            ->whereBetween('date_from', [$start_of_month, $end_of_month])
            ->with(['user'])
            ->orderBy('date_from', 'asc')
            ->get();

        if ($businessTrips->isEmpty()) {
            return redirect()->back()->with('error', 'Nessuna trasferta trovata per il mese e anno specificati.');
        }

        $allTripsData = [];
        $user_vehicle = null;



        foreach ($businessTrips as $businessTrip) {
            $transfers = BusinessTripTransfer::where('business_trip_id', $businessTrip->id)->with(['company'])->get();

            if ($user_vehicle == null) {
                $vehicle_id = $transfers->count() > 0 ? $transfers[0]->vehicle_id : null;
                $user_vehicles = $user->vehicles;

                foreach ($user_vehicles as $vehicle) {
                    if ($vehicle->id == $vehicle_id) {
                        $user_vehicle = $vehicle;
                        break;
                    }
                }
            }

            $transferPairs = $this->generateTransfers($transfers);

            $allTripsData[] = [
                'businessTrip' => $businessTrip,
                'expenses' => BusinessTripExpense::where('business_trip_id', $businessTrip->id)->with(['company'])->get(),
                'transfers' => $transferPairs,
                'user_vehicle' => $user_vehicle,
            ];
        }


        $pdf = PDF::loadView('cedolini.business_trips_batch', [
            'allTripsData' => $allTripsData,
            'month' => $fields['month'],
            'year' => $fields['year'],
            'document_date' => date('Y-m-d'),
            'user_vehicle' => $user_vehicle,
            'user' => $user,
        ]);

        return $pdf->download('trasferte_' . $fields['year'] . '_' . str_pad($fields['month'], 2, '0', STR_PAD_LEFT) . '.pdf');
    }

    private function generateTransfers($transfers) {

        $pairs = [];
        for ($i = 0; $i < count($transfers) - 1; $i++) {
            $pairs[] = [
                'from' => $transfers[$i],
                'to' => $transfers[$i + 1],
            ];
        }

        $result = [];

        foreach ($pairs as $pair) {
            $result[] = [
                'from' => $pair['from'],
                'to' => $pair['to'],
                'azienda' => $pair['to']->company->name,
                'ekm' => round($pair['from']->vehicle->price_per_km, 2),
                'distance' => round($this->routeDistanceGoogle(
                    $pair['from']->latitude,
                    $pair['from']->longitude,
                    $pair['to']->latitude,
                    $pair['to']->longitude
                ), 2),
                'total' => round(
                    $this->routeDistanceGoogle(
                        $pair['from']->latitude,
                        $pair['from']->longitude,
                        $pair['to']->latitude,
                        $pair['to']->longitude
                    ) * $pair['from']->vehicle->price_per_km,
                    2
                ),
            ];
        }
        return $result;
    }

    /**
     * Valida un indirizzo utilizzando l'API di Nominatim.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateAddress(Request $request) {
        // 1. Validazione dei dati di input
        $fields = $request->validate([
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string', // O 'state' a seconda della convenzione usata
            'zip_code' => 'required|string',
        ]);

        // 2. Costruzione della query per Nominatim (utilizzando il parametro 'q')
        // Combiniamo i campi in una singola stringa indirizzo
        $fullAddress = $fields['address'] . ', ' . $fields['city'] . ', ' . $fields['province'] . ', ' . $fields['zip_code'];

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

    // Calcola la distanza stradale in auto tra due coordinate usando Google Routes API (nuova)
    // Restituisce la distanza in km (float) oppure null in caso di errore
    public function routeDistanceGoogle($lat1, $lon1, $lat2, $lon2) {
        $apiKey = config('services.google_maps.api_key'); // Usa la configurazione invece di env()
        if (!$apiKey) {
            Log::error('Google Maps API key mancante.');
            return null;
        }

        $url = 'https://routes.googleapis.com/directions/v2:computeRoutes';
        $body = [
            'origin' => [
                'location' => [
                    'latLng' => [
                        'latitude' => (float)$lat1,
                        'longitude' => (float)$lon1
                    ]
                ]
            ],
            'destination' => [
                'location' => [
                    'latLng' => [
                        'latitude' => (float)$lat2,
                        'longitude' => (float)$lon2
                    ]
                ]
            ],
            'travelMode' => 'DRIVE',
            'routingPreference' => 'TRAFFIC_UNAWARE',
            'units' => 'METRIC',
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Goog-Api-Key' => $apiKey,
                'X-Goog-FieldMask' => 'routes.distanceMeters',
            ])->post($url, $body);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['routes'][0]['distanceMeters'])) {
                    $distanceKm = $data['routes'][0]['distanceMeters'] / 1000;
                    return round($distanceKm, 2);
                } else {
                    Log::error('Risposta Routes API senza distanza valida: ' . json_encode($data));
                }
            } else {
                Log::error('Errore Google Routes API: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Eccezione Google Routes API: ' . $e->getMessage());
        }
        return null;
    }

    /** Caricamento file spese */

    public function uploadExpenseJustification(Request $request, BusinessTripExpense $businessTripExpense) {

        if (!$request->hasFile('justification_file') || !$request->file('justification_file')->isValid()) {
            return back()->with('error', 'Nessun file valido caricato.');
        }
        $file = $request->file('justification_file');

        $file_path = "{$businessTripExpense->businessTrip->code}/spese/{$businessTripExpense->id}/" . time() . '_' . $file->getClientOriginalName() . '.' . $file->getClientOriginalExtension();
        $path = $file->store($file_path, 'gcs');

        $businessTripExpense->update([
            'justification_file_path' => $path,
            'justification_file_name' => $file->getClientOriginalName(),
            'justification_file_mime_type' => $file->getMimeType(),
            'justification_file_size' => $file->getSize(),
            'justification_file_uploaded_at' => now(),
        ]);

        return redirect()->route('business-trips.edit', $businessTripExpense->business_trip_id)->with('success', 'Giustificativo caricato con successo');
    }

    public function downloadExpenseJustification(BusinessTripExpense $businessTripExpense) {
        if (!$businessTripExpense->justification_file_path) {
            return back()->with('error', 'Nessun giustificativo caricato per questa spesa.');
        }
        $disk = Storage::disk('gcs');
        /** @var \Illuminate\Contracts\Filesystem\Cloud|\Spatie\GoogleCloudStorage\GoogleCloudStorageAdapter $disk */
        $url = $disk->temporaryUrl($businessTripExpense->justification_file_path, now()->addMinutes(30));

        return redirect($url);
    }
}
