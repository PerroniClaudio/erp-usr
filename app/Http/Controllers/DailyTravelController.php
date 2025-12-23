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
        $userHeadquarter = $this->getUserHeadquarter($user);
        $selectedCompanyId = $userHeadquarter?->company_id ?? $companies->first()?->id;

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
            Headquarters::with('company')->whereIn('company_id', $companyIds)->get()
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
        $companyIds = $user->companies->pluck('id');

        $validated = $request->validate([
            'travel_date' => ['required', 'date'],
            'intermediate_headquarter_ids' => ['array'],
            'intermediate_headquarter_ids.*' => [
                'integer',
                Rule::exists('headquarters', 'id')->where(fn ($query) => $query->whereIn('company_id', $companyIds)),
            ],
        ]);

        $userHeadquarter = $this->getUserHeadquarter($user);
        if (!$userHeadquarter) {
            return back()
                ->withErrors(['structure' => __('daily_travel.validation_missing_user_headquarter')])
                ->withInput();
        }

        $structure = DailyTravelStructure::where('user_id', $user->id)
            ->where('start_location', DailyTravelStructure::START_LOCATION_OFFICE)
            ->first()
            ?? DailyTravelStructure::where('user_id', $user->id)->first();

        if (!$structure) {
            return back()
                ->withErrors(['structure' => __('daily_travel.validation_no_structure')])
                ->withInput();
        }

        $dailyTravel = DailyTravel::create([
            'user_id' => $user->id,
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

        $dailyTravels = DailyTravel::query()
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
        $distancesBetweenSteps = $this->buildRouteLegs($routeSteps);

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
                Headquarters::with('company')->whereIn('company_id', $companyIds)->get()
            )->toArray();

            $userHeadquarter = $this->getUserHeadquarter($selectedUser);
            $selectedCompanyId = $userHeadquarter?->company_id ?? $companies->first()?->id;
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
        $selectedUser = User::find($request->integer('user_id'));
        $companyIds = $selectedUser?->companies->pluck('id') ?? collect();

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'travel_date' => ['required', 'date'],
            'intermediate_headquarter_ids' => ['array'],
            'intermediate_headquarter_ids.*' => [
                'integer',
                Rule::exists('headquarters', 'id')->where(fn ($query) => $query->whereIn('company_id', $companyIds)),
            ],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $userHeadquarter = $this->getUserHeadquarter($user);
        if (!$userHeadquarter) {
            return back()
                ->withErrors(['structure' => __('daily_travel.validation_missing_user_headquarter')])
                ->withInput();
        }

        $structure = DailyTravelStructure::where('user_id', $user->id)
            ->where('start_location', DailyTravelStructure::START_LOCATION_OFFICE)
            ->first()
            ?? DailyTravelStructure::where('user_id', $user->id)->first();

        if (!$structure) {
            return back()
                ->withErrors(['structure' => __('daily_travel.validation_no_structure')])
                ->withInput();
        }

        $dailyTravel = DailyTravel::create([
            'user_id' => $user->id,
            'daily_travel_structure_id' => $structure->id,
            'travel_date' => $validated['travel_date'],
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
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

    public function adminReview(Request $request, DailyTravel $dailyTravel)
    {
        $dailyTravel->load([
            'user',
            'structure.vehicle',
            'routeSteps' => fn ($q) => $q->orderBy('step_number'),
            'approver',
        ]);

        $routeSteps = $dailyTravel->routeSteps ?? collect();
        $routeLegs = $this->buildRouteLegs($routeSteps, true);

        return view('admin.daily-travels.review', [
            'dailyTravel' => $dailyTravel,
            'structure' => $dailyTravel->structure,
            'routeSteps' => $routeSteps,
            'routeLegs' => $routeLegs,
        ]);
    }

    public function adminApprove(Request $request, DailyTravel $dailyTravel)
    {
        $validated = $request->validate([
            'steps' => ['array'],
            'steps.*.distance_km' => ['nullable', 'numeric', 'min:0'],
            'steps.*.travel_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $stepsPayload = $validated['steps'] ?? [];
        $steps = $dailyTravel->routeSteps()->get()->keyBy('id');

        foreach ($stepsPayload as $stepId => $payload) {
            $step = $steps->get((int) $stepId);
            if (!$step) {
                continue;
            }
            $step->distance_km = $payload['distance_km'] ?? null;
            $step->travel_minutes = $payload['travel_minutes'] ?? null;
            $step->save();
        }

        $dailyTravel->approved_by = $request->user()?->id;
        $dailyTravel->approved_at = now();
        $dailyTravel->save();

        return redirect()
            ->route('admin.daily-travels.review', $dailyTravel)
            ->with('success', __('daily_travel.admin_review_saved'));
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
                        'company_name' => $headquarter->company?->name,
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
            'structure.vehicle',
            'routeSteps' => fn ($q) => $q->orderBy('step_number'),
            'routeSteps.headquarter',
            'user',
            'approver',
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
            [$distance, $travelMinutes] = $this->calculateTravelMetrics($travel, $steps);
            $hasRouteMinutes = $steps
                ->slice(1)
                ->contains(fn ($step) => $step->travel_minutes !== null);
            $travelHours = ($hasRouteMinutes || $travelMinutes > 0)
                ? $travelMinutes / 60
                : (float) ($travel->structure?->travel_hours ?? 0);

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
                'travel_minutes' => $travelMinutes,
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
            'distance_km' => null,
            'travel_minutes' => null,
        ];
    }

    private function buildRouteLegs(Collection $routeSteps, bool $includeMissing = false): array
    {
        $legs = [];

        for ($i = 0; $i < $routeSteps->count() - 1; $i++) {
            $from = $routeSteps[$i];
            $to = $routeSteps[$i + 1];
            $distance = null;

            if ($to->distance_km !== null) {
                $distance = (float) $to->distance_km;
            } elseif ($this->hasValidCoordinates($from) && $this->hasValidCoordinates($to)) {
                $distance = $this->calculateDistanceKm(
                    (float) $from->latitude,
                    (float) $from->longitude,
                    (float) $to->latitude,
                    (float) $to->longitude
                );
            }

            if ($distance !== null || $includeMissing) {
                $legs[] = [
                    'from' => $from,
                    'to' => $to,
                    'distance' => $distance,
                    'travel_minutes' => $to->travel_minutes,
                ];
            }
        }

        return $legs;
    }

    private function calculateRouteDistance(Collection $steps): float
    {
        $distance = 0.0;

        for ($i = 0; $i < $steps->count() - 1; $i++) {
            $from = $steps[$i];
            $to = $steps[$i + 1];

            if ($to->distance_km !== null) {
                $distance += (float) $to->distance_km;
                continue;
            }

            if ($this->hasValidCoordinates($from) && $this->hasValidCoordinates($to)) {
                $distance += $this->calculateDistanceKm(
                    (float) $from->latitude,
                    (float) $from->longitude,
                    (float) $to->latitude,
                    (float) $to->longitude
                );
            }
        }

        return $distance;
    }

    private function calculateRouteMinutes(Collection $steps): int
    {
        return $steps
            ->slice(1)
            ->filter(fn ($step) => $step->travel_minutes !== null)
            ->sum(fn ($step) => (int) $step->travel_minutes);
    }

    private function calculateTravelMetrics(DailyTravel $travel, Collection $steps): array
    {
        if ($steps->count() < 2) {
            return [0.0, 0];
        }

        $legs = $this->buildRouteLegsFromSteps($steps);
        $userHeadquarter = $this->getUserHeadquarter($travel->user);

        if ($userHeadquarter) {
            $this->applyStructureOverride($travel, $userHeadquarter, $legs, true);
            $this->applyStructureOverride($travel, $userHeadquarter, $legs, false);
        }

        $distance = collect($legs)
            ->pluck('distance')
            ->filter(fn ($value) => $value !== null)
            ->sum();

        $minutes = collect($legs)
            ->pluck('travel_minutes')
            ->filter(fn ($value) => $value !== null)
            ->sum();

        return [(float) $distance, (int) $minutes];
    }

    private function buildRouteLegsFromSteps(Collection $steps): array
    {
        $legs = [];

        for ($i = 0; $i < $steps->count() - 1; $i++) {
            $from = $steps[$i];
            $to = $steps[$i + 1];
            $distance = null;

            if ($to->distance_km !== null) {
                $distance = (float) $to->distance_km;
            } elseif ($this->hasValidCoordinates($from) && $this->hasValidCoordinates($to)) {
                $distance = $this->calculateDistanceKm(
                    (float) $from->latitude,
                    (float) $from->longitude,
                    (float) $to->latitude,
                    (float) $to->longitude
                );
            }

            $legs[] = [
                'from' => $from,
                'to' => $to,
                'distance' => $distance,
                'travel_minutes' => $to->travel_minutes !== null ? (int) $to->travel_minutes : null,
            ];
        }

        return $legs;
    }

    private function applyStructureOverride(DailyTravel $travel, Headquarters $userHeadquarter, array &$legs, bool $isFirstLeg): void
    {
        if (empty($legs)) {
            return;
        }

        $index = $isFirstLeg ? 0 : count($legs) - 1;
        $leg = $legs[$index];
        $from = $leg['from'];
        $to = $leg['to'];

        if ($isFirstLeg && (int) $from->headquarter_id !== (int) $userHeadquarter->id) {
            return;
        }

        if (!$isFirstLeg && (int) $to->headquarter_id !== (int) $userHeadquarter->id) {
            return;
        }

        $companyId = $isFirstLeg
            ? $to->headquarter?->company_id
            : $from->headquarter?->company_id;

        if (!$companyId) {
            return;
        }

        $structure = DailyTravelStructure::with('steps')
            ->where('user_id', $travel->user_id)
            ->where('company_id', $companyId)
            ->where('start_location', DailyTravelStructure::START_LOCATION_OFFICE)
            ->first()
            ?? DailyTravelStructure::with('steps')
                ->where('user_id', $travel->user_id)
                ->where('company_id', $companyId)
                ->first();

        if (!$structure) {
            return;
        }

        [$structureDistance, $structureMinutes] = $this->calculateStructureMetrics($structure);

        if ($leg['distance'] === null && $structureDistance !== null) {
            $leg['distance'] = $structureDistance;
        }

        if ($leg['travel_minutes'] === null && $structureMinutes !== null) {
            $leg['travel_minutes'] = $structureMinutes;
        }

        $legs[$index] = $leg;
    }

    private function calculateStructureMetrics(DailyTravelStructure $structure): array
    {
        $steps = $structure->relationLoaded('steps')
            ? $structure->steps
            : $structure->steps()->orderBy('step_number')->get();

        $distance = $this->calculateStructureDistance($steps);

        if (!empty($structure->travel_hours) && (float) $structure->travel_hours > 0) {
            $minutes = (int) round(((float) $structure->travel_hours) * 60);
        } else {
            $minutes = (int) $steps->sum(fn ($step) => (int) ($step->time_difference ?? 0));
        }

        return [$distance, $minutes ?: null];
    }

    private function calculateStructureDistance(Collection $steps): ?float
    {
        if ($steps->count() < 2) {
            return null;
        }

        $distance = 0.0;

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

        return $distance > 0 ? $distance : null;
    }
}
