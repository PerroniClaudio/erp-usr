<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ScheduledTimeOff;
use App\Models\TimeOffRequest;
use App\Models\TimeOffType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WeeklyScheduledTimeOffController extends Controller
{
    public function index(Request $request)
    {
        $currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekStart = $request->has('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek(Carbon::MONDAY)
            : $currentWeekStart->copy()->addWeek();

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $users = User::with(['scheduledTimeOffs' => function ($query) {
            $query->orderByRaw(
                "CASE weekday
                    WHEN 'monday' THEN 1
                    WHEN 'tuesday' THEN 2
                    WHEN 'wednesday' THEN 3
                    WHEN 'thursday' THEN 4
                    WHEN 'friday' THEN 5
                    WHEN 'saturday' THEN 6
                    WHEN 'sunday' THEN 7
                    ELSE 8 END"
            )->orderBy('hour_from');
        }, 'scheduledTimeOffs.timeOffType', 'companies'])->get();

        $dayOffsets = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];

        $scheduledByUser = $users->mapWithKeys(function ($user) use ($weekStart, $dayOffsets) {
            $items = $user->scheduledTimeOffs->map(function ($item) use ($weekStart, $dayOffsets) {
                $date = $weekStart->copy()->addDays($dayOffsets[$item->weekday] ?? 0);

                return [
                    'weekday' => $item->weekday,
                    'date' => $date->toDateString(),
                    'hour_from' => $item->hour_from instanceof Carbon ? $item->hour_from->format('H:i') : $item->hour_from,
                    'hour_to' => $item->hour_to instanceof Carbon ? $item->hour_to->format('H:i') : $item->hour_to,
                    'time_off_type_id' => $item->time_off_type_id,
                    'time_off_type_label' => optional($item->timeOffType)->name,
                ];
            });

            return [$user->id => $items];
        });

        $approvedRequests = TimeOffRequest::with('type')
            ->whereIn('user_id', $users->pluck('id'))
            ->whereIn('status', ['2', 2])
            ->where(function ($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('date_from', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->orWhereBetween('date_to', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->orWhere(function ($inner) use ($weekStart, $weekEnd) {
                        $inner->where('date_from', '<=', $weekStart->toDateString())
                            ->where('date_to', '>=', $weekEnd->toDateString());
                    });
            })
            ->get();

        $existingByUser = $approvedRequests->groupBy('user_id')->map(function ($items) {
            return $items->map(function ($item) {
                return [
                    'weekday' => Carbon::parse($item->date_from)->format('l'),
                    'date' => Carbon::parse($item->date_from)->toDateString(),
                    'hour_from' => Carbon::parse($item->date_from)->format('H:i'),
                    'hour_to' => Carbon::parse($item->date_to)->format('H:i'),
                    'time_off_type_id' => $item->time_off_type_id,
                    'time_off_type_label' => optional($item->type)->name,
                ];
            });
        });

        $timeOffTypes = TimeOffType::orderBy('name')->get();
        $defaultTimeOffTypeId = $timeOffTypes->first()?->id;
        $colorPalette = ['#60a5fa', '#34d399', '#fbbf24', '#a78bfa', '#e73028', '#f472b6', '#2dd4bf', '#437f97'];
        $paletteCount = count($colorPalette);
        $timeOffTypesPayload = $timeOffTypes->values()->map(function ($type, $index) use ($colorPalette, $paletteCount) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'color' => $colorPalette[$index % $paletteCount],
            ];
        });

        return view('admin.personnel.users.weekly-scheduled-timeoff', [
            'users' => $users,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'scheduledByUser' => $scheduledByUser,
            'existingByUser' => $existingByUser,
            'timeOffTypesPayload' => $timeOffTypesPayload,
            'defaultTimeOffTypeId' => $defaultTimeOffTypeId,
            'timeOffTypes' => $timeOffTypes,
        ]);
    }

    public function show(Request $request, User $user)
    {
        $weekStart = $request->has('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $dayOffsets = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];

        $user->load(['scheduledTimeOffs.timeOffType']);

        $scheduleRows = $user->scheduledTimeOffs->map(function ($item) use ($weekStart, $dayOffsets) {
            $date = $weekStart->copy()->addDays($dayOffsets[$item->weekday] ?? 0);
            return [
                'weekday' => $item->weekday,
                'date' => $date->toDateString(),
                'hour_from' => $item->hour_from instanceof Carbon ? $item->hour_from->format('H:i') : $item->hour_from,
                'hour_to' => $item->hour_to instanceof Carbon ? $item->hour_to->format('H:i') : $item->hour_to,
                'time_off_type_id' => $item->time_off_type_id,
                'time_off_type_label' => optional($item->timeOffType)->name,
            ];
        });

        $approvedRequests = TimeOffRequest::with('type')
            ->where('user_id', $user->id)
            ->whereIn('status', ['2', 2])
            ->where(function ($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('date_from', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->orWhereBetween('date_to', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->orWhere(function ($inner) use ($weekStart, $weekEnd) {
                        $inner->where('date_from', '<=', $weekStart->toDateString())
                            ->where('date_to', '>=', $weekEnd->toDateString());
                    });
            })
            ->get()
            ->map(function ($item) {
                return [
                    'weekday' => Carbon::parse($item->date_from)->format('l'),
                    'date' => Carbon::parse($item->date_from)->toDateString(),
                    'hour_from' => Carbon::parse($item->date_from)->format('H:i'),
                    'hour_to' => Carbon::parse($item->date_to)->format('H:i'),
                    'time_off_type_id' => $item->time_off_type_id,
                    'time_off_type_label' => optional($item->type)->name,
                ];
            });

        $timeOffTypes = TimeOffType::orderBy('name')->get();
        $defaultTimeOffTypeId = $timeOffTypes->first()?->id;
        $colorPalette = ['#60a5fa', '#34d399', '#fbbf24', '#a78bfa', '#e73028', '#f472b6', '#2dd4bf', '#437f97'];
        $paletteCount = count($colorPalette);
        $timeOffTypesPayload = $timeOffTypes->values()->map(function ($type, $index) use ($colorPalette, $paletteCount) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'color' => $colorPalette[$index % $paletteCount],
            ];
        });

        $dayLabelsLong = [
            'monday' => __('personnel.users_default_schedule_monday'),
            'tuesday' => __('personnel.users_default_schedule_tuesday'),
            'wednesday' => __('personnel.users_default_schedule_wednesday'),
            'thursday' => __('personnel.users_default_schedule_thursday'),
            'friday' => __('personnel.users_default_schedule_friday'),
            'saturday' => __('personnel.users_default_schedule_saturday'),
            'sunday' => __('personnel.users_default_schedule_sunday'),
        ];
        $dayLabelsShort = [
            'monday' => __('personnel.users_default_schedule_monday_short'),
            'tuesday' => __('personnel.users_default_schedule_tuesday_short'),
            'wednesday' => __('personnel.users_default_schedule_wednesday_short'),
            'thursday' => __('personnel.users_default_schedule_thursday_short'),
            'friday' => __('personnel.users_default_schedule_friday_short'),
            'saturday' => __('personnel.users_default_schedule_saturday_short'),
            'sunday' => __('personnel.users_default_schedule_sunday_short'),
        ];

        $html = view('admin.personnel.users.partials.weekly-scheduled-timeoff-card', [
            'user' => $user,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'scheduleRows' => $scheduleRows,
            'existingRows' => $approvedRequests,
            'dayLabelsLong' => $dayLabelsLong,
            'dayLabelsShort' => $dayLabelsShort,
            'timeOffTypesPayload' => $timeOffTypesPayload,
            'defaultTimeOffTypeId' => $defaultTimeOffTypeId,
            'timeOffTypes' => $timeOffTypes,
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'week_start' => 'required|date',
            'schedule' => 'array',
            'schedule.*.weekday' => 'required|string|in:' . implode(',', ScheduledTimeOff::WEEKDAYS),
            'schedule.*.date' => 'required|date',
            'schedule.*.hour_from' => 'required|date_format:H:i',
            'schedule.*.hour_to' => 'required|date_format:H:i',
            'schedule.*.time_off_type_id' => 'required|exists:time_off_types,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $weekStart = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $batchId = 'scheduled-timeoff-' . $weekStart->toDateString();

        $companyId = $user->companies()->first()?->id ?? Company::where('name', env('MAIN_COMPANY_NAME'))->value('id') ?? Company::first()?->id;
        if (! $companyId) {
            throw ValidationException::withMessages([
                'company_id' => __('personnel.users_scheduled_time_off_missing_company'),
            ]);
        }

        $entries = collect($validated['schedule'] ?? [])->map(function ($item) {
            $start = Carbon::createFromFormat('H:i', $item['hour_from']);
            $end = Carbon::createFromFormat('H:i', $item['hour_to']);

            if ($end->lte($start)) {
                throw ValidationException::withMessages([
                    'hour_to' => __("personnel.users_scheduled_time_off_error_end_before_start"),
                ]);
            }

            return [
                'weekday' => $item['weekday'],
                'date' => $item['date'],
                'hour_from' => $start->format('H:i'),
                'hour_to' => $end->format('H:i'),
                'time_off_type_id' => $item['time_off_type_id'],
            ];
        });

        DB::transaction(function () use ($user, $entries, $batchId, $weekStart, $weekEnd, $companyId) {
            TimeOffRequest::where('user_id', $user->id)
                ->where('batch_id', $batchId)
                ->delete();

            foreach ($entries as $entry) {
                $dateFrom = Carbon::parse("{$entry['date']} {$entry['hour_from']}");
                $dateTo = Carbon::parse("{$entry['date']} {$entry['hour_to']}");

                if ($dateFrom->lt($weekStart)) {
                    $dateFrom = $weekStart->copy();
                }
                if ($dateTo->gt($weekEnd)) {
                    $dateTo = $weekEnd->copy();
                }

                TimeOffRequest::create([
                    'user_id' => $user->id,
                    'company_id' => $companyId,
                    'time_off_type_id' => $entry['time_off_type_id'],
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'status' => 2, // approved
                    'batch_id' => $batchId,
                ]);
            }
        });

        return response()->json(['status' => 'ok']);
    }
}
