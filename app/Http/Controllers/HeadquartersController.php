<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Headquarters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HeadquartersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $companies = Company::orderBy('name')->get();
        $selectedCompanyId = $request->integer('company_id');
        $googleMapsApiKey = config('services.google_maps.api_key');

        return view('admin.headquarters.create', compact('companies', 'selectedCompanyId', 'googleMapsApiKey'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:20'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'company_id' => ['required', 'exists:companies,id'],
        ]);

        $headquarter = Headquarters::create($validated);

        return redirect()
            ->route('companies.edit', $headquarter->company_id)
            ->with('success', __('headquarters.created_success'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Headquarters $headquarters)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Headquarters $headquarter)
    {
        $companies = Company::orderBy('name')->get();
        $googleMapsApiKey = config('services.google_maps.api_key');

        return view('admin.headquarters.edit', compact('headquarter', 'companies', 'googleMapsApiKey'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Headquarters $headquarter)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:20'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'company_id' => ['required', 'exists:companies,id'],
        ]);

        $headquarter->update($validated);

        return redirect()
            ->route('companies.edit', $headquarter->company_id)
            ->with('success', __('headquarters.updated_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Headquarters $headquarter)
    {
        //
    }

    /**
     * Valida un indirizzo usando le Google Maps Geocoding API e restituisce dettagli utili.
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
}
