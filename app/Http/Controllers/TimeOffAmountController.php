<?php

namespace App\Http\Controllers;

use App\Models\TimeOffRequest;
use App\Models\TimeOffType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\TimeOffAmount;

class TimeOffAmountController extends Controller
{
    public function calculateResidual(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'reference_date' => ['required', 'date'],
            'time_off_amount' => ['required', 'numeric', 'min:0'],
            'rol_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $referenceDate = Carbon::parse($validated['reference_date']);
        $periodStart = $referenceDate->copy()->startOfYear();
        $periodEnd = $referenceDate->copy()->endOfMonth();

        $typeIds = TimeOffType::whereIn('name', ['Ferie', 'Rol'])->pluck('id', 'name');

        $timeOffUsed = $this->calculateUsedHours(
            $validated['user_id'],
            $typeIds['Ferie'] ?? null,
            $periodStart,
            $periodEnd
        );

        $rolUsed = $this->calculateUsedHours(
            $validated['user_id'],
            $typeIds['Rol'] ?? null,
            $periodStart,
            $periodEnd
        );

        return response()->json([
            'time_off_used_hours' => round($timeOffUsed, 1),
            'rol_used_hours' => round($rolUsed, 1),
            'time_off_remaining_hours' => round($validated['time_off_amount'] - $timeOffUsed, 1),
            'rol_remaining_hours' => round($validated['rol_amount'] - $rolUsed, 1),
        ]);
    }

    public function getMonthlyAmounts(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:1970'],
        ]);

        $record = TimeOffAmount::where('user_id', $validated['user_id'])
            ->whereYear('reference_date', $validated['year'])
            ->whereMonth('reference_date', $validated['month'])
            ->latest('reference_date')
            ->first();

        return response()->json([
            'time_off_amount' => $record?->time_off_amount ?? 0,
            'rol_amount' => $record?->rol_amount ?? 0,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'insert_date' => ['required', 'date'],
            'reference_date' => ['required', 'date'],
            'time_off_amount' => ['required', 'numeric', 'min:0'],
            'rol_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $referenceDate = Carbon::parse($validated['reference_date']);

        $timeOffAmount = TimeOffAmount::where('user_id', $validated['user_id'])
            ->whereYear('reference_date', $referenceDate->year)
            ->whereMonth('reference_date', $referenceDate->month)
            ->first();

        if ($timeOffAmount) {
            $timeOffAmount->update($validated);
        } else {
            $timeOffAmount = TimeOffAmount::create($validated);
        }

        return response()->json([
            'message' => 'Monte ore salvato correttamente',
            'data' => $timeOffAmount,
        ], 200);
    }

    private function calculateUsedHours(int $userId, ?int $typeId, Carbon $periodStart, Carbon $periodEnd): float
    {
        if (! $typeId) {
            return 0.0;
        }

        $requests = TimeOffRequest::where('user_id', $userId)
            ->where('status', 2) // only approved requests
            ->where('time_off_type_id', $typeId)
            ->where(function ($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('date_from', [$periodStart, $periodEnd])
                    ->orWhereBetween('date_to', [$periodStart, $periodEnd])
                    ->orWhere(function ($q) use ($periodStart, $periodEnd) {
                        $q->where('date_from', '<=', $periodStart)
                            ->where('date_to', '>=', $periodEnd);
                    });
            })
            ->get();

        $totalHours = 0.0;

        foreach ($requests as $request) {
            $requestStart = Carbon::parse($request->date_from);
            $requestEnd = Carbon::parse($request->date_to);

            $start = $requestStart->lt($periodStart) ? $periodStart->copy() : $requestStart;
            $end = $requestEnd->gt($periodEnd) ? $periodEnd->copy() : $requestEnd;

            if ($end->lte($start)) {
                continue;
            }

            $totalHours += $this->calculateRequestHours($request, $start, $end);
        }

        return round($totalHours, 1);
    }

    private function calculateRequestHours(TimeOffRequest $request, Carbon $start, Carbon $end): float
    {
        if ($start->isSameDay($end)) {
            if ($request->date_from && $request->date_to) {
                return $start->diffInMinutes($end) / 60;
            }

            return (float) ($request->hours ?? $request->hours_per_day ?? 8);
        }

        $startDay = $start->copy()->startOfDay();
        $endDay = $end->copy()->startOfDay();

        $days = $startDay->diffInDays($endDay) + 1;
        $hoursPerDay = $request->hours_per_day ?? 8;

        return $hoursPerDay * $days;
    }
}
