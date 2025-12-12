<?php

namespace App\Http\Controllers;

use App\Models\DailyTravel;
use App\Models\DailyTravelStructure;
use App\Models\Headquarters;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

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
        $userHeadquarter = $this->getUserHeadquarter($user);

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

        $structuresMap = $this->buildStructuresMap($structures)
            ->map(fn ($items) => $items->toArray())
            ->toArray();

        $headquartersMap = $this->buildHeadquartersMap(
            Headquarters::whereIn('company_id', $companyIds)->get()
        )->toArray();

        $selectedStructure = $structures
            ->first(fn ($structure) => $structure->company_id === $selectedCompanyId
                && $structure->start_location === DailyTravelStructure::START_LOCATION_OFFICE)
            ?? $structures->firstWhere('company_id', $selectedCompanyId);

        return view('standard.daily-travels.create', [
            'companies' => $companies,
            'selectedCompanyId' => $selectedCompanyId,
            'structuresMap' => $structuresMap,
            'selectedStructure' => $selectedStructure,
            'googleMapsApiKey' => config('services.google_maps.api_key'),
            'headquartersMap' => $headquartersMap,
            'userHeadquarter' => $userHeadquarter,
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
            'intermediate_headquarter_ids' => ['array'],
            'intermediate_headquarter_ids.*' => [
                'integer',
                Rule::exists('headquarters', 'id')->where(fn ($query) => $query->where('company_id', $request->integer('company_id'))),
            ],
        ]);

        $userHeadquarter = $this->getUserHeadquarter($user);
        if (!$userHeadquarter) {
            return back()
                ->withErrors(['company_id' => __('daily_travel.validation_missing_user_headquarter')])
                ->withInput();
        }

        $structure = DailyTravelStructure::where('user_id', $user->id)
            ->where('company_id', $validated['company_id'])
            ->where('start_location', DailyTravelStructure::START_LOCATION_OFFICE)
            ->first();

        if (!$structure) {
            return back()
                ->withErrors(['company_id' => __('daily_travel.validation_no_structure')])
                ->withInput();
        }

        DailyTravel::create([
        $dailyTravel = DailyTravel::create([
            'user_id' => $user->id,
            'company_id' => $validated['company_id'],
            'daily_travel_structure_id' => $structure->id,
            'travel_date' => $validated['travel_date'],
        ]);

        $this->createRouteStepsForTravel(
            $dailyTravel,
            $userHeadquarter,
            $validated['intermediate_headquarter_ids'] ?? []
        );

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

        $structure = $dailyTravel->structure()->with('vehicle')->first();
        $routeSteps = $dailyTravel->routeSteps()->orderBy('step_number')->get();
        $mapSteps = $routeSteps->map(fn ($step) => [
            'step_number' => $step->step_number,
            'lat' => (float) $step->latitude,
            'lng' => (float) $step->longitude,
            'address' => $step->address,
        ])->values();

        $distancesBetweenSteps = [];
        for ($i = 0; $i < $routeSteps->count() - 1; $i++) {
            $from = $routeSteps[$i];
            $to = $routeSteps[$i + 1];

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
            'steps' => $routeSteps,
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

    public function adminCreate(Request $request)
    {
        $users = User::orderBy('name')->get();
        $selectedUserId = $request->old('user_id', $request->integer('user_id'));
        $selectedUser = $users->firstWhere('id', $selectedUserId);

        $companies = collect();
        $companyIds = collect();
        $structures = collect();
        $structuresMap = [];
        $selectedCompanyId = null;
        $headquartersMap = [];
        $userHeadquarter = null;

        if ($selectedUser) {
            $companies = $selectedUser->companies;
            $companyIds = $companies->pluck('id');
            $selectedCompanyId = $request->old('company_id', $request->integer('company_id') ?? $companies->first()?->id);

            if ($selectedCompanyId && !$companyIds->contains($selectedCompanyId)) {
                $selectedCompanyId = $companies->first()?->id;
            }

            $structures = DailyTravelStructure::with([
                'vehicle',
                'steps' => fn ($query) => $query->orderBy('step_number'),
            ])
                ->where('user_id', $selectedUser->id)
                ->whereIn('company_id', $companyIds)
                ->get();

            $structuresMap = $this->buildStructuresMap($structures)
                ->map(fn ($items) => $items->toArray())
                ->toArray();

            $headquartersMap = $this->buildHeadquartersMap(
                Headquarters::whereIn('company_id', $companyIds)->get()
            )->toArray();

            $userHeadquarter = $this->getUserHeadquarter($selectedUser);
        }

        $selectedStructure = $structures
            ->first(fn ($structure) => $structure->company_id === $selectedCompanyId
                && $structure->start_location === DailyTravelStructure::START_LOCATION_OFFICE)
            ?? $structures->firstWhere('company_id', $selectedCompanyId);

        return view('admin.daily-travels.create', [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'companies' => $companies,
            'selectedCompanyId' => $selectedCompanyId,
            'structuresMap' => $structuresMap,
            'selectedStructure' => $selectedStructure,
            'googleMapsApiKey' => config('services.google_maps.api_key'),
            'headquartersMap' => $headquartersMap,
            'userHeadquarter' => $userHeadquarter,
        ]);
    }

    public function adminStore(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'travel_date' => ['required', 'date'],
            'company_id' => [
                'required',
                'integer',
                Rule::exists('user_companies', 'company_id')->where(fn ($query) => $query->where('user_id', $request->integer('user_id'))),
            ],
            'intermediate_headquarter_ids' => ['array'],
            'intermediate_headquarter_ids.*' => [
                'integer',
                Rule::exists('headquarters', 'id')->where(fn ($query) => $query->where('company_id', $request->integer('company_id'))),
            ],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $userHeadquarter = $this->getUserHeadquarter($user);
        if (!$userHeadquarter) {
            return back()
                ->withErrors(['company_id' => __('daily_travel.validation_missing_user_headquarter')])
                ->withInput();
        }

        $structure = DailyTravelStructure::where('user_id', $user->id)
            ->where('company_id', $validated['company_id'])
            ->where('start_location', DailyTravelStructure::START_LOCATION_OFFICE)
            ->first();

        if (!$structure) {
            return back()
                ->withErrors(['company_id' => __('daily_travel.validation_no_structure')])
                ->withInput();
        }

        $dailyTravel = DailyTravel::create([
            'user_id' => $user->id,
            'company_id' => $validated['company_id'],
            'daily_travel_structure_id' => $structure->id,
            'travel_date' => $validated['travel_date'],
        ]);

        $this->createRouteStepsForTravel(
            $dailyTravel,
            $userHeadquarter,
            $validated['intermediate_headquarter_ids'] ?? []
        );

        $travelDate = Carbon::parse($validated['travel_date']);

        return redirect()
            ->route('admin.daily-travels.index', [
                'user_id' => $user->id,
                'month' => $travelDate->format('m'),
                'year' => $travelDate->year,
            ])
            ->with('success', __('daily_travel.created_success'));
    }

    public function adminIndex(Request $request)
    {
        $defaultDate = Carbon::now()->subMonth();

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'month' => ['nullable'],
            'year' => ['nullable', 'integer', 'min:1900'],
        ]);

        $requestedMonth = $validated['month'] ?? null;
        $selectedMonth = str_pad($requestedMonth ?: $defaultDate->format('m'), 2, '0', STR_PAD_LEFT);
        $selectedYear = (int) (($validated['year'] ?? null) ?: $defaultDate->year);

        $selectedUser = null;
        $travelsData = collect();
        $totals = null;

        if (!empty($validated['user_id'])) {
            $selectedUser = User::find($validated['user_id']);

            if ($selectedUser) {
                $monthlyTravels = $this->getMonthlyTravelsForUser($selectedUser, $selectedMonth, $selectedYear);
                [$travelsData, $totals] = $this->summarizeTravels($monthlyTravels);
            }
        }

        $users = User::orderBy('name')->get();

        return view('admin.daily-travels.index', [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'travelsData' => $travelsData,
            'totals' => $totals,
        ]);
    }

    public function adminPdfBatch(Request $request)
    {
        $fields = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'month' => ['required'],
            'year' => ['required', 'integer', 'min:1900'],
        ]);

        $user = User::findOrFail($fields['user_id']);
        $month = str_pad($fields['month'], 2, '0', STR_PAD_LEFT);
        $year = (int) $fields['year'];

        $dailyTravels = $this->getMonthlyTravelsForUser($user, $month, $year);

        if ($dailyTravels->isEmpty()) {
            return back()->with('error', __('daily_travel.batch_empty'));
        }

        [$travelsData, $totals] = $this->summarizeTravels($dailyTravels);

        $pdf = PDF::loadView('cedolini.daily_travels_batch', [
            'travelsData' => $travelsData,
            'month' => $month,
            'year' => $year,
            'document_date' => now()->toDateString(),
            'totals' => $totals,
            'user' => $user,
        ]);

        return $pdf->download('nota_spese_daily_'.$year.'_'.str_pad($month, 2, '0', STR_PAD_LEFT).'.pdf');
    }

    public function pdfBatch(Request $request)
    {
        $fields = $request->validate([
            'month' => 'required',
            'year' => 'required|integer|min:1900',
        ]);

        $user = $request->user();
        $month = str_pad($fields['month'], 2, '0', STR_PAD_LEFT);
        $year = (int) $fields['year'];

        $dailyTravels = $this->getMonthlyTravelsForUser($user, $month, $year);

        if ($dailyTravels->isEmpty()) {
            return back()->with('error', __('daily_travel.batch_empty'));
        }

        [$travelsData, $totals] = $this->summarizeTravels($dailyTravels);

        $pdf = PDF::loadView('cedolini.daily_travels_batch', [
            'travelsData' => $travelsData,
            'month' => $month,
            'year' => $year,
            'document_date' => now()->toDateString(),
            'totals' => $totals,
            'user' => $user,
        ]);

        return $pdf->download('nota_spese_daily_'.$year.'_'.str_pad($month, 2, '0', STR_PAD_LEFT).'.pdf');
    }

    private function buildStructuresMap(Collection $structures): Collection
    {
        return $structures
            ->where('start_location', DailyTravelStructure::START_LOCATION_OFFICE)
            ->groupBy('company_id')
            ->map(function (Collection $items) {
                return $items->mapWithKeys(function (DailyTravelStructure $structure) {
                    return [
                        $structure->start_location => $this->serializeStructure($structure),
                    ];
                });
            });
    }

    private function buildHeadquartersMap(Collection $headquarters): Collection
    {
        return $headquarters
            ->groupBy('company_id')
            ->map(function (Collection $items) {
                return $items->map(function (Headquarters $headquarter) {
                    return [
                        'id' => $headquarter->id,
                        'name' => $headquarter->name,
                        'address' => $headquarter->address,
                        'city' => $headquarter->city,
                        'province' => $headquarter->province,
                        'zip_code' => $headquarter->zip_code,
                        'latitude' => (float) $headquarter->latitude,
                        'longitude' => (float) $headquarter->longitude,
                    ];
                })->values();
            });
    }

    private function serializeStructure(DailyTravelStructure $structure): array
    {
        return [
            'start_location' => $structure->start_location,
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
                'economic_value' => (float) $step->economic_value,
            ])->values(),
        ];
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

    private function getMonthlyTravelsForUser(User $user, string $month, int $year): Collection
    {
        $monthDate = Carbon::createFromDate($year, (int) $month, 1);
        $startOfMonth = $monthDate->copy()->startOfMonth()->toDateString();
        $endOfMonth = $monthDate->copy()->endOfMonth()->toDateString();

        return DailyTravel::with([
            'company',
            'structure.vehicle',
            'routeSteps' => fn ($q) => $q->orderBy('step_number'),
            'user',
        ])
            ->where('user_id', $user->id)
            ->whereBetween('travel_date', [$startOfMonth, $endOfMonth])
            ->orderBy('travel_date')
            ->get();
    }

    private function summarizeTravels(Collection $dailyTravels): array
    {
        $travelsData = $dailyTravels->map(function (DailyTravel $travel) {
            $steps = $travel->routeSteps ?? collect();
            $distance = 0;

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

            $travelHours = (float) ($travel->structure?->travel_hours ?? 0);

            $costPerKm = (float) ($travel->structure?->cost_per_km ?? 0);
            $economicValue = (float) ($travel->structure?->economic_value ?? 0);

            $distanceCost = $distance * $costPerKm;
            $timeCost = $costPerKm * $travelHours;
            $indemnity = $distanceCost + $timeCost;
            $total = $indemnity + $economicValue;

            return [
                'travel' => $travel,
                'distance' => $distance,
                'distance_cost' => $distanceCost,
                'travel_hours' => $travelHours,
                'time_cost' => $timeCost,
                'indemnity' => $indemnity,
                'economic_value' => $economicValue,
                'total' => $total,
                'start_location' => $travel->structure?->start_location,
            ];
        });

        $totals = [
            'distance' => $travelsData->sum('distance'),
            'distance_cost' => $travelsData->sum('distance_cost'),
            'travel_hours' => $travelsData->sum('travel_hours'),
            'time_cost' => $travelsData->sum('time_cost'),
            'indemnity' => $travelsData->sum('indemnity'),
            'economic_value' => $travelsData->sum('economic_value'),
            'grand_total' => $travelsData->sum('total'),
        ];

        return [$travelsData, $totals];
    }

    private function getUserHeadquarter(User $user): ?Headquarters
    {
        return $user->headquarters()->first();
    }

    private function createRouteStepsForTravel(DailyTravel $dailyTravel, Headquarters $userHeadquarter, array $intermediateHeadquarterIds): void
    {
        $orderedIntermediates = collect($intermediateHeadquarterIds)->map(fn ($id) => (int) $id)->filter();
        $intermediateMap = Headquarters::whereIn('id', $orderedIntermediates)->get()->keyBy('id');

        $steps = [];
        $stepNumber = 1;

        $steps[] = $this->normalizeHeadquarter($userHeadquarter, $stepNumber++);

        foreach ($orderedIntermediates as $headquarterId) {
            $headquarter = $intermediateMap->get($headquarterId);
            if ($headquarter) {
                $steps[] = $this->normalizeHeadquarter($headquarter, $stepNumber++);
            }
        }

        $steps[] = $this->normalizeHeadquarter($userHeadquarter, $stepNumber++);

        $dailyTravel->routeSteps()->delete();
        $dailyTravel->routeSteps()->createMany($steps);
    }

    private function normalizeHeadquarter(Headquarters $headquarter, int $stepNumber): array
    {
        return [
            'step_number' => $stepNumber,
            'headquarter_id' => $headquarter->id,
            'name' => $headquarter->name,
            'address' => $headquarter->address,
            'city' => $headquarter->city,
            'province' => $headquarter->province,
            'zip_code' => $headquarter->zip_code,
            'latitude' => (float) $headquarter->latitude,
            'longitude' => (float) $headquarter->longitude,
        ];
    }
}
