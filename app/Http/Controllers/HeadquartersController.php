<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Headquarters;
use App\Support\MapboxAddressParser;
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
        $mapboxAccessToken = config('services.mapbox.access_token');

        return view('admin.headquarters.create', compact('companies', 'selectedCompanyId', 'mapboxAccessToken'));
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
        $mapboxAccessToken = config('services.mapbox.access_token');

        return view('admin.headquarters.edit', compact('headquarter', 'companies', 'mapboxAccessToken'));
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
     * Valida un indirizzo usando Mapbox Geocoding API e restituisce dettagli utili.
     */
    public function searchAddress(Request $request)
    {
        $apiKey = config('services.mapbox.access_token');
        if (!$apiKey) {
            Log::error('Mapbox access token mancante.');

            return response()->json([
                'status' => 'error',
                'message' => 'Token Mapbox mancante.',
            ], 500);
        }

        $fullAddress = $request['address'];
        $queryParams = [
            'access_token' => $apiKey,
            'limit' => 1,
            'types' => 'address',
            'language' => 'it',
            'country' => 'it',
        ];
        $encodedAddress = rawurlencode($fullAddress);

        try {
            $response = Http::get("https://api.mapbox.com/geocoding/v5/mapbox.places/{$encodedAddress}.json", $queryParams);
            if ($response->successful()) {
                $data = $response->json();
                $firstResult = $data['features'][0] ?? null;
                if ($firstResult) {
                    $coordinates = MapboxAddressParser::coordinates($firstResult);
                    $displayName = MapboxAddressParser::displayName($firstResult);
                    $addressDetails = MapboxAddressParser::addressDetails($firstResult);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Indirizzo trovato.',
                        'content' => [
                            'latitude' => $coordinates['latitude'] ?? null,
                            'longitude' => $coordinates['longitude'] ?? null,
                            'display_name' => $displayName,
                            'address_details' => $addressDetails,
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => 'not_found',
                        'message' => 'Indirizzo non trovato.',
                        'data' => null,
                    ], 404); // Codice di stato 404 Not Found
                }
            } else {
                Log::error('Mapbox Geocoding API request failed: '.$response->status());

                return response()->json([
                    'status' => 'error',
                    'message' => 'Errore durante la comunicazione con il servizio di validazione indirizzi.',
                    'details' => $response->body(),
                ], $response->status());

            }
        } catch (\Exception $e) {
            Log::error('Exception during Mapbox Geocoding API call: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Si è verificato un errore imprevisto.',
                'details' => $e->getMessage(),
            ], 500); // Codice di stato 500 Internal Server Error
        }
    }
}
