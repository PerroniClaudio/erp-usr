<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DailyTravelStructure;
use App\Models\User;
use App\Models\DailyTravelStep;
use App\Models\Vehicle;
use App\Support\MapboxAddressParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class DailyTravelStructureController extends Controller
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyTravelStructure $dailyTravelStructure)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, User $user, Company $company)
    {
        $startLocation = DailyTravelStructure::START_LOCATION_OFFICE;
        $vehicles = $user->vehicles()
            ->with(['pricePerKmUpdates' => fn ($query) => $query->latest('update_date')->latest('created_at')])
            ->get();

        $structuresByLocation = $this->ensureStructures($user, $company, $vehicles);
        $dailyTravelStructure = $structuresByLocation[$startLocation];

        $dailyTravelStructure->load([
            'vehicle.pricePerKmUpdates' => fn ($query) => $query->latest('update_date')->latest('created_at'),
        ]);

        $steps = $dailyTravelStructure->steps()->orderBy('step_number')->get();
        $dailyTravelStructure->setRelation('steps', $steps);
        $distancesBetweenSteps = [];
        $mapSteps = $steps->map(fn ($step) => [
            'step_number' => $step->step_number,
            'lat' => (float) $step->latitude,
            'lng' => (float) $step->longitude,
            'address' => $step->address,
        ])->values();

        for ($i = 0; $i < $steps->count() - 1; $i++) {
            $from = $steps[$i];
            $to = $steps[$i + 1];

            if ($this->hasValidCoordinates($from) && $this->hasValidCoordinates($to)) {
                $distancesBetweenSteps[] = [
                    'from' => $from,
                    'to' => $to,
                    'distance' => $this->calculateDistanceKm(
                        (float) $from->latitude,
                        (float) $from->longitude,
                        (float) $to->latitude,
                        (float) $to->longitude
                    ),
                ];
            }
        }


        return view('admin.personnel.users.daily-travel.edit-structure', [
            'user' => $user,
            'company' => $company,
            'dailyTravelStructure' => $dailyTravelStructure,
            'steps' => $steps,
            'vehicles' => $vehicles,
            'distancesBetweenSteps' => $distancesBetweenSteps,
            'mapSteps' => $mapSteps,
            'mapboxAccessToken' => config('services.mapbox.access_token'),
            'startLocation' => $startLocation,
        ]);
           


    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyTravelStructure $dailyTravelStructure)
    {
        //
    }

    public function storeStep(Request $request, User $user, Company $company)
    {
        $dailyTravelStructure = $this->getStructure($user, $company);

        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'time_difference' => 'required|integer|min:0',
            'economic_value' => 'nullable|numeric|min:0',
        ]);

        $nextStepNumber = ($dailyTravelStructure->steps()->max('step_number') ?? 0) + 1;

        $validated['step_number'] = $nextStepNumber;
        $validated['daily_travel_structure_id'] = $dailyTravelStructure->id;
        $validated['economic_value'] = round((float) ($validated['economic_value'] ?? 0), 2);

        if ($coordinates = $this->geocodeWithMapbox($validated)) {
            $validated['latitude'] = $coordinates['latitude'];
            $validated['longitude'] = $coordinates['longitude'];
        }

        DailyTravelStep::create($validated);

        return back()->with('success', __('daily_travel.step_created'));
    }

    public function reorderSteps(Request $request, User $user, Company $company)
    {
        $dailyTravelStructure = $this->getStructure($user, $company);

        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        $stepIds = $dailyTravelStructure->steps()->pluck('id')->toArray();

        // Ensure all provided steps belong to this structure
        if (array_diff($validated['order'], $stepIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alcune tappe non appartengono a questa struttura.',
            ], 422);
        }

        foreach ($validated['order'] as $index => $stepId) {
            DailyTravelStep::where('id', $stepId)->update([
                'step_number' => $index + 1,
            ]);
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function updateVehicle(Request $request, User $user, Company $company)
    {
        $dailyTravelStructure = $this->getStructure($user, $company);

        $validated = $request->validate([
            'vehicle_id' => [
                'required',
                Rule::exists('user_vehicle', 'vehicle_id')->where('user_id', $user->id),
            ],
            'cost_per_km' => ['nullable', 'numeric', 'min:0'],
            'travel_hours' => ['nullable', 'numeric', 'min:0'],
        ]);

        $vehicle = $user->vehicles()
            ->with(['pricePerKmUpdates' => fn ($query) => $query->latest('update_date')->latest('created_at')])
            ->findOrFail($validated['vehicle_id']);

        $cost = $validated['cost_per_km'] ?? $this->getLatestVehicleCost($vehicle);
        $cost = round((float) $cost, 4);
        $travelHours = round((float) ($validated['travel_hours'] ?? $dailyTravelStructure->travel_hours ?? 0), 2);

        $dailyTravelStructure->update([
            'vehicle_id' => $validated['vehicle_id'],
            'cost_per_km' => $cost,
            'travel_hours' => $travelHours,
        ]);

        return back()->with('success', __('daily_travel.vehicle_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyTravelStructure $dailyTravelStructure)
    {
        //
    }

    public function updateStep(Request $request, User $user, Company $company, DailyTravelStep $step)
    {
        $dailyTravelStructure = $this->getStructure($user, $company);

        if ($step->daily_travel_structure_id !== $dailyTravelStructure->id) {
            abort(404);
        }

        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'time_difference' => 'required|integer|min:0',
            'economic_value' => 'nullable|numeric|min:0',
        ]);
        $validated['economic_value'] = round((float) ($validated['economic_value'] ?? 0), 2);

        if ($coordinates = $this->geocodeWithMapbox($validated)) {
            $validated['latitude'] = $coordinates['latitude'];
            $validated['longitude'] = $coordinates['longitude'];
        }

        $step->update($validated);

        return response()->json(['status' => 'success']);
    }

    public function destroyStep(Request $request, User $user, Company $company, DailyTravelStep $step)
    {
        $dailyTravelStructure = $this->getStructure($user, $company);

        if ($step->daily_travel_structure_id !== $dailyTravelStructure->id) {
            abort(404);
        }

        $step->delete();
        $this->renumberSteps($dailyTravelStructure);

        return response()->json(['status' => 'success']);
    }

    private function hasValidCoordinates(DailyTravelStep $step): bool
    {
        return is_numeric($step->latitude) && is_numeric($step->longitude);
    }

    private function calculateDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLon / 2) ** 2;

        $c = 2 * asin(min(1, sqrt($a)));

        return $earthRadiusKm * $c;
    }

    private function renumberSteps(DailyTravelStructure $dailyTravelStructure): void
    {
        $dailyTravelStructure->steps()
            ->orderBy('step_number')
            ->get()
            ->values()
            ->each(function ($step, $index) {
                $step->update(['step_number' => $index + 1]);
            });
    }

    private function ensureStructures(User $user, Company $company, Collection $vehicles): Collection
    {
        $structures = DailyTravelStructure::where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->get()
            ->keyBy('start_location');

        $template = $structures->first();

        foreach (DailyTravelStructure::startLocationOptions() as $location) {
            if (! $structures->has($location)) {
                $structures[$location] = $this->createStructureWithDefaults(
                    $user,
                    $company,
                    $vehicles,
                    $location,
                    $template,
                );

                if (! $template) {
                    $template = $structures[$location];
                }
            }
        }

        return $structures;
    }

    private function createStructureWithDefaults(
        User $user,
        Company $company,
        Collection $vehicles,
        string $startLocation,
        ?DailyTravelStructure $template = null
    ): DailyTravelStructure {
        $vehicle = $template?->vehicle ?? $vehicles->first();
        $vehicleId = $template?->vehicle_id ?? $vehicle?->id;
        $cost = $template?->cost_per_km ?? $this->getLatestVehicleCost($vehicle);
        $travelHours = $template?->travel_hours ?? 0;

        return DailyTravelStructure::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'vehicle_id' => $vehicleId,
            'cost_per_km' => round((float) $cost, 4),
            'start_location' => $startLocation,
            'travel_hours' => round((float) $travelHours, 2),
        ]);
    }

    private function getStructure(User $user, Company $company): DailyTravelStructure
    {
        return DailyTravelStructure::where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->where('start_location', DailyTravelStructure::START_LOCATION_OFFICE)
            ->firstOrFail();
    }

    private function getLatestVehicleCost(?Vehicle $vehicle): float
    {
        if (!$vehicle) {
            return 0.0;
        }

        $latestUpdate = $vehicle->pricePerKmUpdates()
            ->latest('update_date')
            ->latest('created_at')
            ->first();

        $value = $latestUpdate?->price_per_km ?? $vehicle->price_per_km ?? 0;

        return round((float) $value, 4);
    }

    /**
     * Verifica latitudine/longitudine con Mapbox Geocoding API.
     */
    private function geocodeWithMapbox(array $data): ?array
    {
        $apiKey = config('services.mapbox.access_token');
        if (!$apiKey) {
            return null;
        }

        $addressParts = array_filter([
            $data['address'] ?? null,
            $data['zip_code'] ?? null,
            $data['city'] ?? null,
            $data['province'] ?? null,
        ]);

        if (empty($addressParts)) {
            return null;
        }

        $fullAddress = implode(', ', $addressParts);
        $encodedAddress = rawurlencode($fullAddress);

        try {
            $response = Http::get("https://api.mapbox.com/geocoding/v5/mapbox.places/{$encodedAddress}.json", [
                'access_token' => $apiKey,
                'limit' => 1,
                'types' => 'address',
                'language' => 'it',
                'country' => 'it',
            ]);

            if ($response->successful()) {
                $payload = $response->json();
                $feature = $payload['features'][0] ?? null;
                $coordinates = $feature ? MapboxAddressParser::coordinates($feature) : null;

                if ($coordinates) {
                    return $coordinates;
                }

                Log::warning('Mapbox geocoding senza risultati validi', [
                    'address' => $fullAddress,
                ]);
            } else {
                Log::error('Errore HTTP geocoding Mapbox', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Eccezione geocoding Mapbox', [
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }
}
