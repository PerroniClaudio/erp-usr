<?php

namespace App\Http\Controllers;

use App\Models\AttendanceType;
use App\Models\TimeOffRequest;
use App\Models\User;
use App\Models\UserSchedule;
use App\Models\UserScheduleChangeRequest;
use App\Services\NationalHolidayService;
use App\Services\WeeklyScheduleCompletionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class UserScheduleController extends Controller
{
    public function __construct(
        private WeeklyScheduleCompletionService $weeklyScheduleCompletionService,
        private NationalHolidayService $nationalHolidayService
    )
    {
    }

    public function index(Request $request)
    {
        $currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekStart = $request->has('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek(Carbon::MONDAY)
            : $currentWeekStart->copy()->addWeek();

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $holidayDays = $this->nationalHolidayService->getHolidaysBetween($weekStart, $weekEnd);

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

        $scheduledEntries = UserSchedule::with('attendanceType')
            ->whereIn('user_id', $users->pluck('id'))
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('hour_start')
            ->get();

        $existingSchedulesByUser = $scheduledEntries->groupBy('user_id')->map(function ($items) {
            return $items->map(function ($item) {
                return [
                    'day' => $item->day,
                    'date' => $item->date instanceof Carbon ? $item->date->toDateString() : $item->date,
                    'hour_start' => $item->hour_start instanceof Carbon ? $item->hour_start->format('H:i') : $item->hour_start,
                    'hour_end' => $item->hour_end instanceof Carbon ? $item->hour_end->format('H:i') : $item->hour_end,
                    'attendance_type_id' => $item->attendance_type_id,
                    'attendance_type_label' => optional($item->attendanceType)->name,
                ];
            });
        });

        $timeOffRequests = TimeOffRequest::with('type')
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

        $weekStartDay = $weekStart->copy()->startOfDay();
        $weekEndDay = $weekEnd->copy()->endOfDay();

        $timeOffByUser = $timeOffRequests->groupBy('user_id')->map(function ($requests) use ($weekStartDay, $weekEndDay) {
            return $requests->flatMap(function ($request) use ($weekStartDay, $weekEndDay) {
                $requestStart = Carbon::parse($request->date_from);
                $requestEnd = Carbon::parse($request->date_to);

                $rangeStart = $requestStart->greaterThan($weekStartDay) ? $requestStart->copy() : $weekStartDay->copy();
                $rangeEnd = $requestEnd->lessThan($weekEndDay) ? $requestEnd->copy() : $weekEndDay->copy();

                if ($rangeStart->gt($rangeEnd)) {
                    return collect();
                }

                $days = [];
                $currentDay = $rangeStart->copy()->startOfDay();
                $lastDay = $rangeEnd->copy()->startOfDay();
                while ($currentDay->lte($lastDay)) {
                    $dayStart = $currentDay->copy();
                    $dayEnd = $currentDay->copy()->endOfDay();

                    $slotStart = $dayStart->copy();
                    if ($rangeStart->gt($slotStart)) {
                        $slotStart = $rangeStart->copy();
                    }
                    if ($requestStart->gt($slotStart)) {
                        $slotStart = $requestStart->copy();
                    }

                    $slotEnd = $dayEnd->copy();
                    if ($rangeEnd->lt($slotEnd)) {
                        $slotEnd = $rangeEnd->copy();
                    }
                    if ($requestEnd->lt($slotEnd)) {
                        $slotEnd = $requestEnd->copy();
                    }

                    if ($slotEnd->gt($slotStart)) {
                        $days[] = [
                            'start' => $slotStart->toIso8601String(),
                            'end' => $slotEnd->toIso8601String(),
                            'title' => $request->type->name ?? __('attendances.time_off'),
                            'color' => $request->getColorForRequest(0),
                        ];
                    }

                    $currentDay->addDay();
                }

                return $days;
            })->values();
        });

        $attendanceTypes = AttendanceType::orderBy('name')->get();
        $defaultAttendanceTypeId = $this->determineDefaultAttendanceTypeId($attendanceTypes);
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
            'holidayDays' => $holidayDays,
            'defaultSchedulesByUser' => $defaultSchedulesByUser,
            'attendanceTypes' => $attendanceTypes,
            'defaultAttendanceTypeId' => $defaultAttendanceTypeId,
            'attendanceTypesPayload' => $attendanceTypesPayload,
            'existingSchedulesByUser' => $existingSchedulesByUser,
            'timeOffByUser' => $timeOffByUser,
        ]);
    }

    public function show(Request $request, User $user)
    {
        $weekStart = $request->has('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $holidayDays = $this->nationalHolidayService->getHolidaysBetween($weekStart, $weekEnd);


        $dayOffsets = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];

        $user->load(['defaultSchedules' => function ($query) {
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
        }, 'defaultSchedules.attendanceType']);

        $defaultSchedules = $user->defaultSchedules->map(function ($item) use ($weekStart, $dayOffsets) {
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

        $existingSchedules = UserSchedule::with('attendanceType')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('hour_start')
            ->get()
            ->map(function ($item) {
                return [
                    'day' => $item->day,
                    'date' => $item->date instanceof Carbon ? $item->date->toDateString() : $item->date,
                    'hour_start' => $item->hour_start instanceof Carbon ? $item->hour_start->format('H:i') : $item->hour_start,
                    'hour_end' => $item->hour_end instanceof Carbon ? $item->hour_end->format('H:i') : $item->hour_end,
                    'attendance_type_id' => $item->attendance_type_id,
                    'attendance_type_label' => optional($item->attendanceType)->name,
                ];
            });

        $hasExisting = $existingSchedules->isNotEmpty();
        $scheduleRows = $hasExisting ? $existingSchedules : $defaultSchedules;

        $timeOffRequests = TimeOffRequest::with('type')
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
            ->get();

        $weekStartDay = $weekStart->copy()->startOfDay();
        $weekEndDay = $weekEnd->copy()->endOfDay();

        $timeOffEntries = $timeOffRequests->flatMap(function ($request) use ($weekStartDay, $weekEndDay) {
            $requestStart = Carbon::parse($request->date_from);
            $requestEnd = Carbon::parse($request->date_to);

            $rangeStart = $requestStart->greaterThan($weekStartDay) ? $requestStart->copy() : $weekStartDay->copy();
            $rangeEnd = $requestEnd->lessThan($weekEndDay) ? $requestEnd->copy() : $weekEndDay->copy();

            if ($rangeStart->gt($rangeEnd)) {
                return collect();
            }

            $days = [];
            $currentDay = $rangeStart->copy()->startOfDay();
            $lastDay = $rangeEnd->copy()->startOfDay();
            while ($currentDay->lte($lastDay)) {
                $dayStart = $currentDay->copy();
                $dayEnd = $currentDay->copy()->endOfDay();

                $slotStart = $dayStart->copy();
                if ($rangeStart->gt($slotStart)) {
                    $slotStart = $rangeStart->copy();
                }
                if ($requestStart->gt($slotStart)) {
                    $slotStart = $requestStart->copy();
                }

                $slotEnd = $dayEnd->copy();
                if ($rangeEnd->lt($slotEnd)) {
                    $slotEnd = $rangeEnd->copy();
                }
                if ($requestEnd->lt($slotEnd)) {
                    $slotEnd = $requestEnd->copy();
                }

                if ($slotEnd->gt($slotStart)) {
                    $days[] = [
                        'start' => $slotStart->toIso8601String(),
                        'end' => $slotEnd->toIso8601String(),
                        'title' => $request->type->name ?? __('attendances.time_off'),
                        'color' => $request->getColorForRequest(0),
                    ];
                }

                $currentDay->addDay();
            }

            return $days;
        });

        $attendanceTypes = AttendanceType::orderBy('name')->get();
        $defaultAttendanceTypeId = $this->determineDefaultAttendanceTypeId($attendanceTypes);
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

        $html = view('admin.personnel.users.partials.weekly-schedule-card', [
            'user' => $user,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'scheduleRows' => $scheduleRows,
            'hasExisting' => $hasExisting,
            'timeOffEntries' => $timeOffEntries,
            'dayLabelsLong' => $dayLabelsLong,
            'dayLabelsShort' => $dayLabelsShort,
            'attendanceTypes' => $attendanceTypes,
            'attendanceTypesPayload' => $attendanceTypesPayload,
            'defaultAttendanceTypeId' => $defaultAttendanceTypeId,
            'holidayDays' => $holidayDays,
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function requestForm(Request $request)
    {
        $user = $request->user();

        $currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $minimumWeekStart = $currentWeekStart->copy()->addWeek();

        $weekStart = $request->has('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek(Carbon::MONDAY)
            : $minimumWeekStart->copy();

        if ($weekStart->lte($currentWeekStart)) {
            $weekStart = $minimumWeekStart->copy();
        }

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $holidayDays = $this->nationalHolidayService->getHolidaysBetween($weekStart, $weekEnd);


        $dayOffsets = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];

        $user->load(['defaultSchedules' => function ($query) {
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
        }, 'defaultSchedules.attendanceType']);

        $defaultSchedules = $user->defaultSchedules->map(function ($item) use ($weekStart, $dayOffsets) {
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

        $pendingRequest = UserScheduleChangeRequest::where('user_id', $user->id)
            ->whereDate('week_start', $weekStart->toDateString())
            ->where('status', UserScheduleChangeRequest::STATUS_PENDING)
            ->latest()
            ->first();

        $scheduleRows = $pendingRequest && !empty($pendingRequest->schedule)
            ? collect($pendingRequest->schedule)
            : $defaultSchedules;

        $attendanceTypes = AttendanceType::orderBy('name')->get();
        $defaultAttendanceTypeId = $this->determineDefaultAttendanceTypeId($attendanceTypes);
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

        return view('standard.user-schedules.request', [
            'user' => $user,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'holidayDays' => $holidayDays,
            'scheduleRows' => $scheduleRows,
            'attendanceTypes' => $attendanceTypes,
            'defaultAttendanceTypeId' => $defaultAttendanceTypeId,
            'attendanceTypesPayload' => $attendanceTypesPayload,
            'dayLabelsLong' => $dayLabelsLong,
            'dayLabelsShort' => $dayLabelsShort,
            'pendingRequest' => $pendingRequest,
            'minimumWeekStart' => $minimumWeekStart,
        ]);
    }

    public function submitRequest(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'week_start' => 'required|date',
            'schedule' => 'required|array|min:1',
            'schedule.*.day' => 'required|string|in:' . implode(',', UserSchedule::DAYS),
            'schedule.*.date' => 'required|date',
            'schedule.*.hour_start' => 'required|date_format:H:i',
            'schedule.*.hour_end' => 'required|date_format:H:i',
            'schedule.*.attendance_type_id' => 'required|exists:attendance_types,id',
        ]);

        $weekStart = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::MONDAY);
        $currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        if ($weekStart->lte($currentWeekStart)) {
            throw ValidationException::withMessages([
                'week_start' => __('personnel.user_schedule_request_week_error'),
            ]);
        }
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $entries = collect($validated['schedule'])->map(function ($item) {
            $start = Carbon::createFromFormat('H:i', $item['hour_start']);
            $end = Carbon::createFromFormat('H:i', $item['hour_end']);

            if ($end->lte($start)) {
                throw ValidationException::withMessages([
                    'schedule' => __("personnel.users_default_schedule_error_end_before_start"),
                ]);
            }

            return [
                'day' => $item['day'],
                'date' => $item['date'],
                'hour_start' => $start->format('H:i'),
                'hour_end' => $end->format('H:i'),
                'total_hours' => $start->diffInMinutes($end) / 60,
                'attendance_type_id' => $item['attendance_type_id'],
            ];
        });

        if ($entries->isEmpty()) {
            throw ValidationException::withMessages([
                'schedule' => __("personnel.user_schedule_request_empty_error"),
            ]);
        }

        $this->ensureNoHolidayConflicts($entries, $weekStart, $weekEnd);

        UserScheduleChangeRequest::updateOrCreate(
            [
                'user_id' => $user->id,
                'week_start' => $weekStart->toDateString(),
                'status' => UserScheduleChangeRequest::STATUS_PENDING,
            ],
            [
                'week_end' => $weekEnd->toDateString(),
                'schedule' => $entries->toArray(),
                'status' => UserScheduleChangeRequest::STATUS_PENDING,
            ]
        );

        return redirect()->route('user-schedule-request.index', ['week_start' => $weekStart->toDateString()])
            ->with('success', __('personnel.user_schedule_request_success'));
    }

    public function adminRequestsIndex()
    {
        $pendingRequests = UserScheduleChangeRequest::with('user')
            ->where('status', UserScheduleChangeRequest::STATUS_PENDING)
            ->orderBy('week_start')
            ->get();

        $processedRequests = UserScheduleChangeRequest::with('user')
            ->whereIn('status', [
                UserScheduleChangeRequest::STATUS_APPROVED,
                UserScheduleChangeRequest::STATUS_DENIED,
            ])
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();

        return view('admin.personnel.users.schedule-change-requests.index', [
            'pendingRequests' => $pendingRequests,
            'processedRequests' => $processedRequests,
        ]);
    }

    public function adminRequestsShow(UserScheduleChangeRequest $userScheduleChangeRequest)
    {
        $userScheduleChangeRequest->load('user');

        $weekStart = Carbon::parse($userScheduleChangeRequest->week_start)->startOfWeek(Carbon::MONDAY);
        $weekEnd = Carbon::parse($userScheduleChangeRequest->week_end ?? $weekStart->copy()->endOfWeek(Carbon::SUNDAY));
        $holidayDays = $this->nationalHolidayService->getHolidaysBetween($weekStart, $weekEnd);

        $scheduleRows = collect($userScheduleChangeRequest->schedule ?? []);

        $attendanceTypes = AttendanceType::orderBy('name')->get();
        $defaultAttendanceTypeId = $this->determineDefaultAttendanceTypeId($attendanceTypes);
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

        $canApprove = $userScheduleChangeRequest->status === UserScheduleChangeRequest::STATUS_PENDING;

        return view('admin.personnel.users.schedule-change-requests.show', [
            'changeRequest' => $userScheduleChangeRequest,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'holidayDays' => $holidayDays,
            'scheduleRows' => $scheduleRows,
            'attendanceTypes' => $attendanceTypes,
            'defaultAttendanceTypeId' => $defaultAttendanceTypeId,
            'attendanceTypesPayload' => $attendanceTypesPayload,
            'dayLabelsLong' => $dayLabelsLong,
            'dayLabelsShort' => $dayLabelsShort,
            'canApprove' => $canApprove,
        ]);
    }

    public function adminRequestsApprove(Request $request, UserScheduleChangeRequest $userScheduleChangeRequest)
    {
        if ($userScheduleChangeRequest->status !== UserScheduleChangeRequest::STATUS_PENDING) {
            return redirect()->route('admin.user-schedule-requests.index')->with('error', __('personnel.user_schedule_request_admin_invalid_state'));
        }

        $validated = $request->validate([
            'schedule' => 'required|array|min:1',
            'schedule.*.day' => 'required|string|in:' . implode(',', UserSchedule::DAYS),
            'schedule.*.date' => 'required|date',
            'schedule.*.hour_start' => 'required|date_format:H:i',
            'schedule.*.hour_end' => 'required|date_format:H:i',
            'schedule.*.attendance_type_id' => 'required|exists:attendance_types,id',
        ]);

        $entries = collect($validated['schedule'])->map(function ($item) use ($userScheduleChangeRequest) {
            $start = Carbon::createFromFormat('H:i', $item['hour_start']);
            $end = Carbon::createFromFormat('H:i', $item['hour_end']);

            if ($end->lte($start)) {
                throw ValidationException::withMessages([
                    'schedule' => __('personnel.users_default_schedule_error_end_before_start'),
                ]);
            }

            return [
                'user_id' => $userScheduleChangeRequest->user_id,
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

        $weekStart = Carbon::parse($userScheduleChangeRequest->week_start)->startOfWeek(Carbon::MONDAY);
        $weekEnd = Carbon::parse($userScheduleChangeRequest->week_end ?? $weekStart->copy()->endOfWeek(Carbon::SUNDAY));

        if ($entries->isEmpty()) {
            throw ValidationException::withMessages([
                'schedule' => __('personnel.user_schedule_request_empty_error'),
            ]);
        }

        $this->ensureNoHolidayConflicts($entries, $weekStart, $weekEnd);

        UserSchedule::where('user_id', $userScheduleChangeRequest->user_id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->delete();

        UserSchedule::insert($entries->toArray());

        $this->weeklyScheduleCompletionService->refreshForWeek($weekStart);

        $userScheduleChangeRequest->update([
            'schedule' => $entries->map(function ($entry) {
                return [
                    'day' => $entry['day'],
                    'date' => $entry['date'],
                    'hour_start' => $entry['hour_start'],
                    'hour_end' => $entry['hour_end'],
                    'total_hours' => $entry['total_hours'],
                    'attendance_type_id' => $entry['attendance_type_id'],
                ];
            })->toArray(),
            'status' => UserScheduleChangeRequest::STATUS_APPROVED,
        ]);

        return redirect()->route('admin.user-schedule-requests.index')->with('success', __('personnel.user_schedule_request_admin_success'));
    }

    public function adminRequestsDeny(UserScheduleChangeRequest $userScheduleChangeRequest)
    {
        if ($userScheduleChangeRequest->status !== UserScheduleChangeRequest::STATUS_PENDING) {
            return redirect()->route('admin.user-schedule-requests.index')->with('error', __('personnel.user_schedule_request_admin_invalid_state'));
        }

        $userScheduleChangeRequest->update([
            'status' => UserScheduleChangeRequest::STATUS_DENIED,
        ]);

        return redirect()->route('admin.user-schedule-requests.index')->with('success', __('personnel.user_schedule_request_admin_denied'));
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
        $this->ensureNoHolidayConflicts($entries, $weekStart, $weekEnd);

        // Replace schedules for that week and user to avoid duplicates
        UserSchedule::where('user_id', $userId)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->delete();

        if ($entries->isNotEmpty()) {
            UserSchedule::insert($entries->toArray());
        }

        $this->weeklyScheduleCompletionService->refreshForWeek($weekStart);

        return redirect()->route('user-schedules.index', ['week_start' => $weekStart->toDateString()])
            ->with('success', __('personnel.users_default_schedule_save'));
    }

    private function determineDefaultAttendanceTypeId(Collection $attendanceTypes): ?int
    {
        if ($attendanceTypes->isEmpty()) {
            return null;
        }

        $preferred = $attendanceTypes->first(function ($type) {
            $name = strtolower($type->name ?? '');
            $acronym = strtolower($type->acronym ?? '');

            return $name === 'lavoro in sede' || $acronym === 'ls';
        });

        return $preferred?->id ?? $attendanceTypes->first()?->id;
    }

    private function ensureNoHolidayConflicts(Collection $entries, Carbon $weekStart, Carbon $weekEnd): void
    {
        if ($entries->isEmpty()) {
            return;
        }

        $holidayDates = $this->nationalHolidayService
            ->getHolidaysBetween($weekStart, $weekEnd)
            ->pluck('date')
            ->filter()
            ->unique()
            ->all();

        if (empty($holidayDates)) {
            return;
        }

        $holidayLookup = array_flip($holidayDates);

        $hasConflict = $entries->contains(function ($entry) use ($holidayLookup) {
            $date = $entry['date'] ?? null;
            return $date && isset($holidayLookup[$date]);
        });

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'schedule' => __('personnel.users_weekly_schedule_holiday_error'),
            ]);
        }
    }
}
