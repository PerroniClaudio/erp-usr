<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
use App\Models\BusinessTripExpense;
use App\Models\BusinessTripTransfer;
use App\Models\User;
use App\Support\MapboxAddressParser;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BusinessTripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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
    public function create()
    {
        //

        $companies = Auth::user()->companies;

        return view('standard.business_trips.create', [
            'companies' => $companies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $fields = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $code = date('Y').'TRA'.str_pad(BusinessTrip::count() + 1, 5, '0', STR_PAD_LEFT);

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
    public function show(BusinessTrip $businessTrip)
    {
        //

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusinessTrip $businessTrip)
    {
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

    private function cleanupTransfers($transfers)
    {

        foreach ($transfers as $transfer) {
            if ($transfer->address == 'N/A') {

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

    private function resolveAddressFromCoordinates($latitude, $longitude)
    {
        $apiKey = config('services.mapbox.access_token');
        if (!$apiKey) {
            Log::error('Mapbox access token mancante.');

            return [
                'address' => null,
                'city' => null,
                'province' => null,
                'zip_code' => null,
            ];
        }

        $response = Http::get("https://api.mapbox.com/geocoding/v5/mapbox.places/{$longitude},{$latitude}.json", [
            'access_token' => $apiKey,
            'limit' => 1,
            'types' => 'address',
            'language' => 'it',
            'country' => 'it',
        ]);

        if (!$response->successful()) {
            return [
                'address' => null,
                'city' => null,
                'province' => null,
                'zip_code' => null,
            ];
        }

        $data = $response->json();
        $feature = $data['features'][0] ?? null;
        if (!$feature) {
            return [
                'address' => null,
                'city' => null,
                'province' => null,
                'zip_code' => null,
            ];
        }

        $details = MapboxAddressParser::addressDetails($feature);
        $addressParts = array_filter([
            $details['road'] ?? null,
            $details['house_number'] ?? null,
        ]);

        return [
            'address' => $addressParts ? implode(' ', $addressParts) : 'N/A',
            'city' => $details['city'] ?? 'N/A',
            'province' => $details['county'] ?? 'N/A',
            'zip_code' => $details['postcode'] ?? 'N/A',
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessTrip $businessTrip)
    {
        //

        $fields = $request->validate([
            'date_from' => 'required|string',
            'date_to' => 'required|string',
            'status' => 'required|integer',
        ]);

        // Verifica se si sta tentando di cambiare lo status
        if ($request->status != $businessTrip->status) {
            // Calcola la data limite per modificare lo status (primo giorno del mese successivo + 1 giorno)
            $businessTripDate = \Carbon\Carbon::parse($businessTrip->date_from);
            $deadlineDate = $businessTripDate->copy()->startOfMonth()->addMonth()->addDay();

            // Se la data limite è già passata, impedisci il cambio di status
            if (now()->isAfter($deadlineDate)) {
                return back()->with('error', 'Non è possibile modificare lo status di una trasferta più vecchia di un mese. Scadenza: '.$deadlineDate->format('d/m/Y'));
            }
        }

        $businessTrip->update($fields);

        return back()->with('success', 'Trasferta aggiornata con successo');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessTrip $businessTrip)
    {
        //

        $businessTrip->update([
            'status' => 2,
        ]);

        return back()->with('success', 'Trasferta eliminata con successo');
    }

    public function adminIndex(Request $request)
    {
        $defaultDate = Carbon::now()->subMonth();

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'month' => ['nullable'],
            'year' => ['nullable', 'integer', 'min:1900'],
        ]);

        $selectedMonth = str_pad($validated['month'] ?? $defaultDate->format('m'), 2, '0', STR_PAD_LEFT);
        $selectedYear = (int) ($validated['year'] ?? $defaultDate->year);

        $selectedUser = null;
        $businessTrips = collect();
        $totals = null;

        if (!empty($validated['user_id'])) {
            $selectedUser = User::find($validated['user_id']);

            if ($selectedUser) {
                $businessTrips = $this->getMonthlyBusinessTrips($selectedUser, $selectedMonth, $selectedYear);
                $totals = [
                    'trips' => $businessTrips->count(),
                    'expenses' => $businessTrips->sum(fn ($trip) => (float) $trip->expenses->sum('amount')),
                    'transfers' => $businessTrips->sum(fn ($trip) => $trip->transfers->count()),
                ];
            }
        }

        $users = User::orderBy('name')->get();

        return view('admin.business-trips.index', [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'businessTrips' => $businessTrips,
            'totals' => $totals,
        ]);
    }

    /**
     * * SPESE *
     */
    public function getExpenses(BusinessTrip $businessTrip)
    {
        $expenses = BusinessTripExpense::where('business_trip_id', $businessTrip->id)->with(['company'])->get();

        return response([
            'expenses' => $expenses,
        ], 200);
    }

    public function createExpense(BusinessTrip $businessTrip)
    {
        $companies = Auth::user()->companies;

        return view('standard.business_trips.expenses.create', [
            'businessTrip' => $businessTrip,
            'companies' => $companies,
        ]);
    }

    public function editExpense(BusinessTrip $businessTrip, BusinessTripExpense $expense)
    {
        $companies = Auth::user()->companies;

        return view('standard.business_trips.expenses.edit', [
            'businessTrip' => $businessTrip,
            'expense' => $expense,
            'companies' => $companies,
        ]);
    }

    public function updateExpense(BusinessTrip $businessTrip, BusinessTripExpense $expense, Request $request)
    {

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

    public function storeExpense(BusinessTrip $businessTrip, Request $request)
    {

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

    public function destroyExpense(BusinessTrip $businessTrip, BusinessTripExpense $expense)
    {
        //

        $expense->delete();

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Spesa eliminata con successo');
    }

    /**
     * * TRASFERIMENTI *
     */
    public function createTransfer(BusinessTrip $businessTrip)
    {
        $companies = Auth::user()->companies;
        $userVehicles = Auth::user()->vehicles;
        $userVehicles = $userVehicles->map(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'name' => $vehicle->brand.' '.$vehicle->model.' - '.$vehicle->pivot->plate_number,
            ];
        });

        return view('standard.business_trips.transfers.create', [
            'businessTrip' => $businessTrip,
            'companies' => $companies,
            'userVehicles' => $userVehicles,
        ]);
    }

    public function editTransfer(BusinessTrip $businessTrip, BusinessTripTransfer $transfer)
    {
        $companies = Auth::user()->companies;
        $userVehicles = Auth::user()->vehicles;
        $userVehicles = $userVehicles->map(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'name' => $vehicle->brand.' '.$vehicle->model.' - '.$vehicle->pivot->plate_number,
            ];
        });

        return view('standard.business_trips.transfers.edit', [
            'businessTrip' => $businessTrip,
            'transfer' => $transfer,
            'companies' => $companies,
            'userVehicles' => $userVehicles,
        ]);
    }

    public function getTransfers(BusinessTrip $businessTrip)
    {
        $transfers = BusinessTripTransfer::where('business_trip_id', $businessTrip->id)->with(['company'])->get();

        return response([
            'transfers' => $transfers,
        ], 200);
    }

    public function storeTransfer(BusinessTrip $businessTrip, Request $request)
    {

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

    public function updateTransfer(BusinessTrip $businessTrip, BusinessTripTransfer $transfer, Request $request)
    {

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

    public function destroyTransfer(BusinessTrip $businessTrip, BusinessTripTransfer $transfer)
    {

        $transfer->delete();

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Trasferimento eliminato con successo');
    }

    public function generatePdf(BusinessTrip $businessTrip)
    {

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

        return $pdf->download('trasferta_'.$businessTrip->code.'.pdf');
    }

    public function generateMonthlyPdf(Request $request)
    {
        $fields = $request->validate([
            'month' => 'required',
            'year' => 'required|integer|min:1900',
        ]);

        $user = $request->user();
        $month = str_pad($fields['month'], 2, '0', STR_PAD_LEFT);
        $year = (int) $fields['year'];

        $businessTrips = $this->getMonthlyBusinessTrips($user, $month, $year);

        if ($businessTrips->isEmpty()) {
            return redirect()->back()->with('error', 'Nessuna trasferta trovata per il mese e anno specificati.');
        }

        [$allTripsData, $userVehicle] = $this->buildMonthlyBatchData($businessTrips, $user);

        $pdf = PDF::loadView('cedolini.business_trips_batch', [
            'allTripsData' => $allTripsData,
            'month' => $month,
            'year' => $year,
            'document_date' => date('Y-m-d'),
            'user_vehicle' => $userVehicle,
            'user' => $user,
        ]);

        return $pdf->download('trasferte_'.$year.'_'.str_pad($month, 2, '0', STR_PAD_LEFT).'.pdf');
    }

    private function generateTransfers($transfers)
    {

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
                'distance' => round($this->routeDistanceMapbox(
                    $pair['from']->latitude,
                    $pair['from']->longitude,
                    $pair['to']->latitude,
                    $pair['to']->longitude
                ), 2),
                'total' => round(
                    $this->routeDistanceMapbox(
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

    public function adminGenerateMonthlyPdf(Request $request)
    {
        $fields = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'month' => 'required',
            'year' => 'required|integer|min:1900',
        ]);

        $user = User::findOrFail($fields['user_id']);
        $month = str_pad($fields['month'], 2, '0', STR_PAD_LEFT);
        $year = (int) $fields['year'];

        $businessTrips = $this->getMonthlyBusinessTrips($user, $month, $year);

        if ($businessTrips->isEmpty()) {
            return redirect()->back()->with('error', 'Nessuna trasferta trovata per il mese e anno specificati.');
        }

        [$allTripsData, $userVehicle] = $this->buildMonthlyBatchData($businessTrips, $user);

        $pdf = PDF::loadView('cedolini.business_trips_batch', [
            'allTripsData' => $allTripsData,
            'month' => $month,
            'year' => $year,
            'document_date' => date('Y-m-d'),
            'user_vehicle' => $userVehicle,
            'user' => $user,
        ]);

        return $pdf->download('trasferte_'.$year.'_'.str_pad($month, 2, '0', STR_PAD_LEFT).'.pdf');
    }

    private function getMonthlyBusinessTrips(User $user, string $month, int $year): Collection
    {
        $monthDate = Carbon::createFromDate($year, (int) $month, 1);
        $startOfMonth = $monthDate->copy()->startOfMonth()->toDateString();
        $endOfMonth = $monthDate->copy()->endOfMonth()->toDateString();

        return BusinessTrip::where('user_id', $user->id)
            ->whereBetween('date_from', [$startOfMonth, $endOfMonth])
            ->with([
                'user',
                'expenses',
                'transfers.company',
                'transfers.vehicle',
            ])
            ->orderBy('date_from', 'asc')
            ->get();
    }

    private function buildMonthlyBatchData(Collection $businessTrips, User $user): array
    {
        $allTripsData = [];
        $userVehicle = null;

        foreach ($businessTrips as $businessTrip) {
            $transfers = $businessTrip->transfers;

            if ($userVehicle === null) {
                $vehicleId = $transfers->count() > 0 ? $transfers[0]->vehicle_id : null;
                $userVehicles = $user->vehicles;

                foreach ($userVehicles as $vehicle) {
                    if ($vehicle->id == $vehicleId) {
                        $userVehicle = $vehicle;
                        break;
                    }
                }
            }

            $transferPairs = $this->generateTransfers($transfers);

            $allTripsData[] = [
                'businessTrip' => $businessTrip,
                'expenses' => $businessTrip->expenses,
                'transfers' => $transferPairs,
                'user_vehicle' => $userVehicle,
            ];
        }

        return [$allTripsData, $userVehicle];
    }

    /**
     * Valida un indirizzo utilizzando Mapbox Geocoding API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateAddress(Request $request)
    {
        // 1. Validazione dei dati di input
        $fields = $request->validate([
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string', // O 'state' a seconda della convenzione usata
            'zip_code' => 'required|string',
        ]);

        $apiKey = config('services.mapbox.access_token');
        if (!$apiKey) {
            Log::error('Mapbox access token mancante.');

            return response()->json([
                'status' => 'error',
                'message' => 'Token Mapbox mancante.',
            ], 500);
        }

        $fullAddress = $fields['address'].', '.$fields['city'].', '.$fields['province'].', '.$fields['zip_code'];
        $encodedAddress = rawurlencode($fullAddress);
        $queryParams = [
            'access_token' => $apiKey,
            'limit' => 1,
            'types' => 'address',
            'language' => 'it',
            'country' => 'it',
        ];

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

    // Calcola la distanza stradale in auto tra due coordinate usando Mapbox Directions API
    // Restituisce la distanza in km (float) oppure null in caso di errore
    public function routeDistanceMapbox($lat1, $lon1, $lat2, $lon2)
    {
        $apiKey = config('services.mapbox.access_token');
        if (! $apiKey) {
            Log::error('Mapbox access token mancante.');

            return null;
        }

        $coordinates = sprintf('%s,%s;%s,%s', $lon1, $lat1, $lon2, $lat2);

        try {
            $response = Http::get("https://api.mapbox.com/directions/v5/mapbox/driving/{$coordinates}", [
                'access_token' => $apiKey,
                'overview' => 'false',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['routes'][0]['distance'])) {
                    $distanceKm = $data['routes'][0]['distance'] / 1000;

                    return round($distanceKm, 2);
                } else {
                    Log::error('Risposta Mapbox Directions senza distanza valida: '.json_encode($data));
                }
            } else {
                Log::error('Errore Mapbox Directions API: '.$response->body());
            }
        } catch (\Exception $e) {
            Log::error('Eccezione Mapbox Directions API: '.$e->getMessage());
        }

        return null;
    }

    /** Caricamento file spese */
    public function uploadExpenseJustification(Request $request, BusinessTripExpense $businessTripExpense)
    {

        if (! $request->hasFile('justification_file') || ! $request->file('justification_file')->isValid()) {
            return back()->with('error', 'Nessun file valido caricato.');
        }
        $file = $request->file('justification_file');

        $file_path = "trasferte/{$businessTripExpense->businessTrip->code}/spese/{$businessTripExpense->id}/".time().'_'.$file->getClientOriginalName().'.'.$file->getClientOriginalExtension();
        $path = $file->store($file_path, 's3');

        $businessTripExpense->update([
            'justification_file_path' => $path,
            'justification_file_name' => $file->getClientOriginalName(),
            'justification_file_mime_type' => $file->getMimeType(),
            'justification_file_size' => $file->getSize(),
            'justification_file_uploaded_at' => now(),
        ]);

        return redirect()->route('business-trips.edit', $businessTripExpense->business_trip_id)->with('success', 'Giustificativo caricato con successo');
    }

    public function downloadExpenseJustification(BusinessTripExpense $businessTripExpense)
    {
        if (! $businessTripExpense->justification_file_path) {
            return back()->with('error', 'Nessun giustificativo caricato per questa spesa.');
        }
        $disk = Storage::disk('s3');
        /** @var \Illuminate\Contracts\Filesystem\Cloud|\Illuminate\Filesystem\AwsS3V3Adapter $disk */
        $url = $disk->temporaryUrl($businessTripExpense->justification_file_path, now()->addMinutes(30));

        return redirect($url);
    }
}
