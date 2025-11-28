<?php

namespace App\Http\Controllers;

use App\Models\DailyTravel;
use App\Models\DailyTravelStructure;
use App\Models\DailyTravelExpense;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class DailyTravelController extends Controller
{
    /**
     * Show the form for creating a new daily travel.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $companies = $user->companies;
        $companyIds = $companies->pluck('id');
        $selectedCompanyId = $request->integer('company_id') ?? $companies->first()?->id;

        if ($selectedCompanyId && !$companyIds->contains($selectedCompanyId)) {
            $selectedCompanyId = $companies->first()?->id;
        }

        $structures = DailyTravelStructure::with([
            'vehicle',
            'steps' => fn ($query) => $query->orderBy('step_number'),
        ])
            ->where('user_id', $user->id)
            ->whereIn('company_id', $companyIds)
            ->get();

        $structuresMap = $structures->mapWithKeys(function (DailyTravelStructure $structure) {
            return [
                $structure->company_id => [
                    'cost_per_km' => (float) $structure->cost_per_km,
                    'economic_value' => (float) $structure->economic_value,
                    'vehicle' => $structure->vehicle ? [
                        'id' => $structure->vehicle->id,
                        'label' => trim($structure->vehicle->brand.' '.$structure->vehicle->model),
                        'price_per_km' => (float) $structure->vehicle->price_per_km,
                    ] : null,
                    'steps' => $structure->steps->map(fn ($step) => [
                        'step_number' => $step->step_number,
                        'address' => $step->address,
                        'city' => $step->city,
                        'province' => $step->province,
                        'zip_code' => $step->zip_code,
                        'latitude' => (float) $step->latitude,
                        'longitude' => (float) $step->longitude,
                        'time_difference' => (int) $step->time_difference,
                    ])->values(),
                ],
            ];
        });

        return view('standard.daily-travels.create', [
            'companies' => $companies,
            'selectedCompanyId' => $selectedCompanyId,
            'structuresMap' => $structuresMap,
            'selectedStructure' => $structures->firstWhere('company_id', $selectedCompanyId),
            'googleMapsApiKey' => config('services.google_maps.api_key'),
        ]);
    }

    /**
     * Store a newly created daily travel.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'travel_date' => ['required', 'date'],
            'company_id' => [
                'required',
                'integer',
                Rule::exists('user_companies', 'company_id')->where(fn ($query) => $query->where('user_id', $user->id)),
            ],
        ]);

        $structure = DailyTravelStructure::where('user_id', $user->id)
            ->where('company_id', $validated['company_id'])
            ->first();

        if (!$structure) {
            return back()
                ->withErrors(['company_id' => __('daily_travel.validation_no_structure')])
                ->withInput();
        }

        DailyTravel::create([
            'user_id' => $user->id,
            'company_id' => $validated['company_id'],
            'daily_travel_structure_id' => $structure->id,
            'travel_date' => $validated['travel_date'],
        ]);

        return redirect()
            ->route('daily-travels.index')
            ->with('success', __('daily_travel.created_success'));
    }

    /**
     * Display the user's daily travels.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $dailyTravels = DailyTravel::with(['company'])
            ->where('user_id', $user->id)
            ->orderByDesc('travel_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('standard.daily-travels.index', [
            'dailyTravels' => $dailyTravels,
        ]);
    }

    public function show(Request $request, DailyTravel $dailyTravel)
    {
        $user = $request->user();
        if ($dailyTravel->user_id !== $user->id) {
            abort(404);
        }

        $structure = $dailyTravel->structure()->with(['vehicle', 'steps' => fn ($q) => $q->orderBy('step_number')])->first();

        $steps = $structure?->steps ?? collect();
        $mapSteps = $steps->map(fn ($step) => [
            'step_number' => $step->step_number,
            'lat' => (float) $step->latitude,
            'lng' => (float) $step->longitude,
            'address' => $step->address,
        ])->values();

        $distancesBetweenSteps = [];
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

        return view('standard.daily-travels.show', [
            'dailyTravel' => $dailyTravel,
            'structure' => $structure,
            'steps' => $steps,
            'mapSteps' => $mapSteps,
            'distancesBetweenSteps' => $distancesBetweenSteps,
            'googleMapsApiKey' => config('services.google_maps.api_key'),
        ]);
    }

    public function destroy(Request $request, DailyTravel $dailyTravel)
    {
        $user = $request->user();
        if ($dailyTravel->user_id !== $user->id) {
            abort(404);
        }

        $dailyTravel->delete();

        return redirect()
            ->route('daily-travels.index')
            ->with('success', __('daily_travel.deleted_success'));
    }

    private function hasValidCoordinates($step): bool
    {
        return is_numeric($step->latitude ?? null) && is_numeric($step->longitude ?? null);
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


    public function pdfBatch(Request $request)
    {
        $fields = $request->validate([
            'month' => 'required',
            'year' => 'required|integer|min:1900',
        ]);

        $startOfMonth = date('Y-m-d', strtotime($fields['year'].'-'.$fields['month'].'-01'));
        $endOfMonth = date('Y-m-t', strtotime($fields['year'].'-'.$fields['month'].'-01'));

        $user = $request->user();

        $dailyTravels = DailyTravel::with([
            'company',
            'structure.vehicle',
            'structure.steps' => fn ($q) => $q->orderBy('step_number'),
        ])
            ->where('user_id', $user->id)
            ->whereBetween('travel_date', [$startOfMonth, $endOfMonth])
            ->orderBy('travel_date')
            ->get();

        if ($dailyTravels->isEmpty()) {
            return back()->with('error', __('daily_travel.batch_empty'));
        }

        $travelsData = $dailyTravels->map(function (DailyTravel $travel) {
            $steps = $travel->structure?->steps ?? collect();
            $distance = 0;
            $timeDifference = 0;
            for ($i = 0; $i < $steps->count() - 1; $i++) {
                $from = $steps[$i];
                $to = $steps[$i + 1];

                if ($this->hasValidCoordinates($from) && $this->hasValidCoordinates($to)) {
                    $distance += $this->calculateDistanceKm(
                        (float) $from->latitude,
                        (float) $from->longitude,
                        (float) $to->latitude,
                        (float) $to->longitude
                    );
                }
            }

            $timeDifference = $steps->sum(fn ($step) => (int) ($step->time_difference ?? 0));

            $costPerKm = (float) ($travel->structure?->cost_per_km ?? 0);
            $economicValue = (float) ($travel->structure?->economic_value ?? 0);
            $distanceCost = $distance * $costPerKm;
            $timeCost = $costPerKm * $timeDifference;
            $indemnity = $distanceCost + $timeCost;
            $total = $indemnity + $economicValue;

            return [
                'travel' => $travel,
                'distance' => $distance,
                'distance_cost' => $distanceCost,
                'time_difference' => $timeDifference,
                'time_cost' => $timeCost,
                'indemnity' => $indemnity,
                'economic_value' => $economicValue,
                'total' => $total,
            ];
        });

        $totals = [
            'distance' => $travelsData->sum('distance'),
            'distance_cost' => $travelsData->sum('distance_cost'),
            'time_difference' => $travelsData->sum('time_difference'),
            'time_cost' => $travelsData->sum('time_cost'),
            'indemnity' => $travelsData->sum('indemnity'),
            'economic_value' => $travelsData->sum('economic_value'),
            'grand_total' => $travelsData->sum('total'),
        ];

        $pdf = PDF::loadView('cedolini.daily_travels_batch', [
            'travelsData' => $travelsData,
            'month' => $fields['month'],
            'year' => $fields['year'],
            'document_date' => now()->toDateString(),
            'totals' => $totals,
            'user' => $user,
        ]);

        return $pdf->download('nota_spese_daily_'.$fields['year'].'_'.str_pad($fields['month'], 2, '0', STR_PAD_LEFT).'.pdf');
    }
}
