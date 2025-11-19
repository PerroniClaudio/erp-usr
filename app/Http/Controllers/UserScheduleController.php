<?php

namespace App\Http\Controllers;

use App\Models\AttendanceType;
use App\Models\User;
use App\Models\UserSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserScheduleController extends Controller
{
    public function index(Request $request)
    {
        $weekStart = $request->has('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $users = User::with(['defaultSchedules' => function ($query) {
            $query->orderByRaw(
                "CASE day
                    WHEN 'monday' THEN 1
                    WHEN 'tuesday' THEN 2
                    WHEN 'wednesday' THEN 3
                    WHEN 'thursday' THEN 4
                    WHEN 'friday' THEN 5
                    WHEN 'saturday' THEN 6
                    WHEN 'sunday' THEN 7
                    ELSE 8 END"
            )->orderBy('hour_start');
        }, 'defaultSchedules.attendanceType'])->get();

        $dayOffsets = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];

        $defaultSchedulesByUser = $users->mapWithKeys(function ($user) use ($weekStart, $dayOffsets) {
            $items = $user->defaultSchedules->map(function ($item) use ($weekStart, $dayOffsets) {
                $date = $weekStart->copy()->addDays($dayOffsets[$item->day] ?? 0);
                return [
                    'day' => $item->day,
                    'date' => $date->toDateString(),
                    'hour_start' => $item->hour_start instanceof Carbon ? $item->hour_start->format('H:i') : $item->hour_start,
                    'hour_end' => $item->hour_end instanceof Carbon ? $item->hour_end->format('H:i') : $item->hour_end,
                    'attendance_type_id' => $item->attendance_type_id,
                    'attendance_type_label' => optional($item->attendanceType)->name,
                ];
            });

            return [$user->id => $items];
        });

        $attendanceTypes = AttendanceType::orderBy('name')->get();
        $defaultAttendanceTypeId = $attendanceTypes->first()?->id;
        $colorPalette = ['#60a5fa', '#34d399', '#fbbf24', '#a78bfa', '#e73028', '#f472b6', '#2dd4bf', '#437f97'];
        $paletteCount = count($colorPalette);
        $attendanceTypesPayload = $attendanceTypes->values()->map(function ($type, $index) use ($colorPalette, $paletteCount) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'acronym' => $type->acronym,
                'color' => $colorPalette[$index % $paletteCount],
            ];
        });

        return view('admin.personnel.users.weekly-schedules', [
            'users' => $users,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'defaultSchedulesByUser' => $defaultSchedulesByUser,
            'attendanceTypes' => $attendanceTypes,
            'defaultAttendanceTypeId' => $defaultAttendanceTypeId,
            'attendanceTypesPayload' => $attendanceTypesPayload,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'week_start' => 'required|date',
            'schedule' => 'array',
            'schedule.*.day' => 'required|string|in:' . implode(',', UserSchedule::DAYS),
            'schedule.*.date' => 'required|date',
            'schedule.*.hour_start' => 'required|date_format:H:i',
            'schedule.*.hour_end' => 'required|date_format:H:i',
            'schedule.*.attendance_type_id' => 'required|exists:attendance_types,id',
        ]);

        $userId = $validated['user_id'];
        $weekStart = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $entries = collect($validated['schedule'] ?? [])->map(function ($item) {
            $start = Carbon::createFromFormat('H:i', $item['hour_start']);
            $end = Carbon::createFromFormat('H:i', $item['hour_end']);

            if ($end->lte($start)) {
                throw ValidationException::withMessages([
                    'schedule' => __("personnel.users_default_schedule_error_end_before_start"),
                ]);
            }

            return [
                'user_id' => request('user_id'),
                'day' => $item['day'],
                'date' => $item['date'],
                'hour_start' => $start->format('H:i'),
                'hour_end' => $end->format('H:i'),
                'total_hours' => $start->diffInMinutes($end) / 60,
                'attendance_type_id' => $item['attendance_type_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        // Replace schedules for that week and user to avoid duplicates
        UserSchedule::where('user_id', $userId)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->delete();

        if ($entries->isNotEmpty()) {
            UserSchedule::insert($entries->toArray());
        }

        return redirect()->route('user-schedules.index', ['week_start' => $weekStart->toDateString()])
            ->with('success', __('personnel.users_default_schedule_save'));
    }
}
