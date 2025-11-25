<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DailyTravelStructure;
use App\Models\User;
use App\Models\DailyTravelStep;
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
    public function edit(User $user, Company $company)
    {
        
        // Controlla se esiste già un DailyTravelStructure per l'utente e l'azienda specificati
        $dailyTravelStructure = DailyTravelStructure::where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->first();

        // Se non esiste, creane uno nuovo
        if (!$dailyTravelStructure) {
            $dailyTravelStructure = DailyTravelStructure::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'vehicle_id' => $user->vehicles()->first()?->id,
                'cost_per_km' => 0,
                'economic_value' => 0,
                'travel_minutes' => 0,
            ]);
        }

        // A questo punto fetcha tutti gli step 

        $steps = $dailyTravelStructure->steps;


        return view('admin.personnel.users.daily-travel.edit-structure', [
            'user' => $user,
            'company' => $company,
            'dailyTravelStructure' => $dailyTravelStructure,
            'steps' => $steps,
            'vehicles' => $user->vehicles,
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
        $dailyTravelStructure = DailyTravelStructure::where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->firstOrFail();

        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $nextStepNumber = ($dailyTravelStructure->steps()->max('step_number') ?? 0) + 1;

        $validated['step_number'] = $nextStepNumber;
        $validated['daily_travel_structure_id'] = $dailyTravelStructure->id;

        DailyTravelStep::create($validated);

        return back()->with('success', __('daily_travel.step_created'));
    }

    public function reorderSteps(Request $request, User $user, Company $company)
    {
        $dailyTravelStructure = DailyTravelStructure::where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->firstOrFail();

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
        $dailyTravelStructure = DailyTravelStructure::where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->firstOrFail();

        $validated = $request->validate([
            'vehicle_id' => [
                'required',
                Rule::exists('user_vehicle', 'vehicle_id')->where('user_id', $user->id),
            ],
            'cost_per_km' => ['nullable', 'numeric', 'min:0'],
            'economic_value' => ['nullable', 'numeric', 'min:0'],
            'travel_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $cost = $validated['cost_per_km'] ?? $dailyTravelStructure->vehicle?->price_per_km ?? 0;
        $cost = round((float) $cost, 2);

        $dailyTravelStructure->update([
            'vehicle_id' => $validated['vehicle_id'],
            'cost_per_km' => $cost,
            'economic_value' => $validated['economic_value'] ?? 0,
            'travel_minutes' => $validated['travel_minutes'] ?? 0,
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
}
