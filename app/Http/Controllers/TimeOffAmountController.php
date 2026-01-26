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

        $yearTotalRecord = $this->getYearTotalRecord($validated['user_id'], $referenceDate->year);
        $isResidualFallback = false;
        if (! $yearTotalRecord) {
            $yearTotalRecord = TimeOffAmount::where('user_id', $validated['user_id'])
                ->whereDate(
                    'reference_date',
                    Carbon::create($referenceDate->year, 12, 31)->toDateString()
                )
                ->first();
            $isResidualFallback = (bool) $yearTotalRecord;
        }

        $timeOffTotal = $yearTotalRecord
            ? (float) $yearTotalRecord->time_off_amount
            : (float) $validated['time_off_amount'];
        $rolTotal = $yearTotalRecord
            ? (float) $yearTotalRecord->rol_amount
            : (float) $validated['rol_amount'];

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

        $timeOffRemaining = $isResidualFallback ? $timeOffTotal : $timeOffTotal - $timeOffUsed;
        $rolRemaining = $isResidualFallback ? $rolTotal : $rolTotal - $rolUsed;

        return response()->json([
            'time_off_used_hours' => round($timeOffUsed, 1),
            'rol_used_hours' => round($rolUsed, 1),
            'time_off_remaining_hours' => round($timeOffRemaining, 1),
            'rol_remaining_hours' => round($rolRemaining, 1),
        ]);
    }

    public function getMonthlyAmounts(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:1970'],
        ]);

        $record = $this->getYearTotalRecord($validated['user_id'], $validated['year']);
        if (! $record) {
            $record = TimeOffAmount::where('user_id', $validated['user_id'])
                ->whereDate('reference_date', Carbon::create($validated['year'], 12, 31)->toDateString())
                ->first();
        }

        return response()->json([
            'time_off_amount' => $record?->time_off_amount ?? 0,
            'rol_amount' => $record?->rol_amount ?? 0,
        ]);
    }

    public function getMonthlyUsage(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'reference_date' => ['required', 'date'],
        ]);

        $referenceDate = Carbon::parse($validated['reference_date']);
        $year = $referenceDate->year;
        $limitMonth = $referenceDate->month;

        $periodStart = $referenceDate->copy()->startOfYear();
        $periodEnd = $referenceDate->copy()->endOfMonth();

        $typeIds = TimeOffType::whereIn('name', ['Ferie', 'Rol'])->pluck('id', 'name');

        $requests = TimeOffRequest::where('user_id', $validated['user_id'])
            ->where('status', 2)
            ->where(function ($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('date_from', [$periodStart, $periodEnd])
                    ->orWhereBetween('date_to', [$periodStart, $periodEnd])
                    ->orWhere(function ($q) use ($periodStart, $periodEnd) {
                        $q->where('date_from', '<=', $periodStart)
                            ->where('date_to', '>=', $periodEnd);
                    });
            })
            ->whereIn('time_off_type_id', $typeIds)
            ->get();

        $yearTotalRecord = $this->getYearTotalRecord($validated['user_id'], $year);
        $isResidualFallback = false;
        if (! $yearTotalRecord) {
            $yearTotalRecord = TimeOffAmount::where('user_id', $validated['user_id'])
                ->whereDate('reference_date', Carbon::create($year, 12, 31)->toDateString())
                ->first();
            $isResidualFallback = (bool) $yearTotalRecord;
        }

        $yearTimeOffTotal = $yearTotalRecord ? (float) $yearTotalRecord->time_off_amount : 0.0;
        $yearRolTotal = $yearTotalRecord ? (float) $yearTotalRecord->rol_amount : 0.0;

        $labels = [];
        $ferieData = [];
        $rolData = [];
        $ferieAmounts = [];
        $rolAmounts = [];
        $ferieUsedCumulative = 0.0;
        $rolUsedCumulative = 0.0;

        for ($month = 1; $month <= $limitMonth; $month++) {
            $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $ferieTotal = 0.0;
            $rolTotal = 0.0;

            foreach ($requests as $request) {
                $requestStart = Carbon::parse($request->date_from);
                $requestEnd = Carbon::parse($request->date_to);

                if ($requestEnd->lt($monthStart) || $requestStart->gt($monthEnd)) {
                    continue;
                }

                $start = $requestStart->lt($monthStart) ? $monthStart : $requestStart;
                $end = $requestEnd->gt($monthEnd) ? $monthEnd : $requestEnd;

                $hours = $this->calculateRequestHours($request, $start, $end);

                if ($request->time_off_type_id == ($typeIds['Ferie'] ?? null)) {
                    $ferieTotal += $hours;
                } elseif ($request->time_off_type_id == ($typeIds['Rol'] ?? null)) {
                    $rolTotal += $hours;
                }
            }

            $ferieUsedCumulative += $ferieTotal;
            $rolUsedCumulative += $rolTotal;

            $labels[] = $monthStart->locale('it')->shortMonthName;
            $ferieData[] = round($ferieTotal, 1);
            $rolData[] = round($rolTotal, 1);
            if ($isResidualFallback) {
                $ferieAmounts[] = round($yearTimeOffTotal, 1);
                $rolAmounts[] = round($yearRolTotal, 1);
            } else {
                $ferieAmounts[] = round($yearTimeOffTotal - $ferieUsedCumulative, 1);
                $rolAmounts[] = round($yearRolTotal - $rolUsedCumulative, 1);
            }
        }

        return response()->json([
            'labels' => $labels,
            'ferie' => $ferieData,
            'rol' => $rolData,
            'ferie_amounts' => $ferieAmounts,
            'rol_amounts' => $rolAmounts,
            'is_residual_fallback' => $isResidualFallback,
        ]);
    }

    public function getUserBalance(Request $request)
    {
        $user = $request->user();
        $referenceDate = Carbon::parse($request->input('reference_date', now()->toDateString()));
        $periodStart = $referenceDate->copy()->startOfYear();
        $periodEnd = $referenceDate->copy()->endOfDay();

        $yearTotalRecord = $this->getYearTotalRecord($user->id, $referenceDate->year);
        $isResidualFallback = false;
        if (! $yearTotalRecord) {
            $yearTotalRecord = TimeOffAmount::where('user_id', $user->id)
                ->whereDate(
                    'reference_date',
                    Carbon::create($referenceDate->year, 12, 31)->toDateString()
                )
                ->first();
            $isResidualFallback = (bool) $yearTotalRecord;
        }

        $timeOffTotal = $yearTotalRecord ? (float) $yearTotalRecord->time_off_amount : 0.0;
        $rolTotal = $yearTotalRecord ? (float) $yearTotalRecord->rol_amount : 0.0;

        $typeIds = TimeOffType::whereIn('name', ['Ferie', 'Rol'])->pluck('id', 'name');

        $timeOffUsed = $this->calculateUsedHours(
            $user->id,
            $typeIds['Ferie'] ?? null,
            $periodStart,
            $periodEnd
        );

        $rolUsed = $this->calculateUsedHours(
            $user->id,
            $typeIds['Rol'] ?? null,
            $periodStart,
            $periodEnd
        );

        $timeOffRemaining = $isResidualFallback ? $timeOffTotal : $timeOffTotal - $timeOffUsed;
        $rolRemaining = $isResidualFallback ? $rolTotal : $rolTotal - $rolUsed;

        return response()->json([
            'time_off_used_hours' => round($timeOffUsed, 1),
            'rol_used_hours' => round($rolUsed, 1),
            'time_off_remaining_hours' => round($timeOffRemaining, 1),
            'rol_remaining_hours' => round($rolRemaining, 1),
            'is_residual_fallback' => $isResidualFallback,
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
        $isYearStart = $referenceDate->isSameDay($referenceDate->copy()->startOfYear());

        $timeOffAmount = TimeOffAmount::where('user_id', $validated['user_id'])
            ->whereDate('reference_date', $referenceDate->toDateString())
            ->first();

        if ($isYearStart) {
            $previousResidual = TimeOffAmount::where('user_id', $validated['user_id'])
                ->whereDate(
                    'reference_date',
                    Carbon::create($referenceDate->year - 1, 12, 31)->toDateString()
                )
                ->first();

            if ($previousResidual) {
                $validated['time_off_amount'] =
                    (float) $validated['time_off_amount'] + (float) $previousResidual->time_off_amount;
                $validated['rol_amount'] =
                    (float) $validated['rol_amount'] + (float) $previousResidual->rol_amount;
            }
        }

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

    private function getYearTotalRecord(int $userId, int $year): ?TimeOffAmount
    {
        return TimeOffAmount::where('user_id', $userId)
            ->whereDate('reference_date', Carbon::create($year, 1, 1)->toDateString())
            ->first();
    }

    private function calculateRequestHours(TimeOffRequest $request, Carbon $start, Carbon $end): float
    {
        if ($end->lte($start)) {
            return 0.0;
        }

        return $start->diffInMinutes($end) / 60;
    }
}
