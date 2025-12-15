<?php

namespace App\Services;

use App\Models\ScheduledTimeOff;
use App\Models\TimeOffRequest;
use App\Models\User;
use App\Models\UserSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserWeeklyCalendarService
{
    private const TIME_OFF_COLORS = [
        '#60a5fa',
        '#34d399',
        '#fbbf24',
        '#a78bfa',
        '#e73028',
        '#f472b6',
        '#2dd4bf',
        '#437f97',
    ];

    public function __construct(private NationalHolidayService $nationalHolidayService)
    {
    }

    /**
     * @return array{week_start: \Carbon\Carbon, week_end: \Carbon\Carbon, days: \Illuminate\Support\Collection<int, array>}
     */
    public function buildForUser(User $user, Carbon $weekStart, ?Carbon $weekEnd = null): array
    {
        $start = $weekStart->copy()->startOfWeek(Carbon::MONDAY);
        $end = $weekEnd ? $weekEnd->copy()->endOfWeek(Carbon::SUNDAY) : $start->copy()->endOfWeek(Carbon::SUNDAY);

        $holidayLabels = $this->nationalHolidayService
            ->getHolidaysBetween($start, $end)
            ->mapWithKeys(function (array $holiday) {
                $date = Carbon::parse($holiday['date'])->toDateString();

                return [$date => $holiday['label'] ?? __('home.national_holiday')];
            });

        $presencesByDate = $this->getScheduledPresences($user, $start, $end);
        $timeOffByDate = $this->getPlannedTimeOff($user, $start, $end);

        $days = collect(range(0, 6))->map(function (int $offset) use ($start, $holidayLabels, $presencesByDate, $timeOffByDate) {
            $day = $start->copy()->addDays($offset);
            $dateKey = $day->toDateString();

            return [
                'date' => $day,
                'holiday' => $holidayLabels[$dateKey] ?? null,
                'presences' => $presencesByDate[$dateKey] ?? collect(),
                'time_off' => $timeOffByDate[$dateKey] ?? collect(),
            ];
        });

        return [
            'week_start' => $start,
            'week_end' => $end,
            'days' => $days,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, array>>
     */
    private function getScheduledPresences(User $user, Carbon $start, Carbon $end): Collection
    {
        $schedules = UserSchedule::with('attendanceType')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('hour_start')
            ->get();

        return $schedules->groupBy(function ($item) {
            return $item->date instanceof Carbon ? $item->date->toDateString() : $item->date;
        })->map(function ($items) {
            return $items->map(function ($item) {
                $start = $this->formatTime($item->hour_start);
                $end = $this->formatTime($item->hour_end);
                $interval = trim("{$start} - {$end}");

                return [
                    'label' => $interval,
                    'type' => $item->attendanceType?->acronym ?? $item->attendanceType?->name,
                    'color' => $item->attendanceType?->color ?? '#60a5fa',
                    'start' => $this->mergeDateAndTime($this->parseDate($item->date), $item->hour_start),
                    'end' => $this->mergeDateAndTime($this->parseDate($item->date), $item->hour_end),
                ];
            })->values();
        });
    }

    /**
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, array>>
     */
    private function getPlannedTimeOff(User $user, Carbon $start, Carbon $end): Collection
    {
        $dayOffsets = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];

        $scheduledTimeOff = $user->scheduledTimeOffs()
            ->with('timeOffType')
            ->get();

        $approvedRequests = TimeOffRequest::with('type')
            ->where('user_id', $user->id)
            ->whereIn('status', ['2', 2])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('date_from', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('date_to', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($inner) use ($start, $end) {
                        $inner->where('date_from', '<=', $start->toDateString())
                            ->where('date_to', '>=', $end->toDateString());
                    });
            })
            ->get();

        $typeColor = $this->assignColors(
            $scheduledTimeOff->pluck('time_off_type_id')
                ->merge($approvedRequests->pluck('time_off_type_id'))
                ->filter()
                ->unique()
        );

        $weekStartDay = $start->copy()->startOfDay();
        $weekEndDay = $end->copy()->endOfDay();

        $scheduledEntries = $scheduledTimeOff->map(function (ScheduledTimeOff $item) use ($dayOffsets, $start, $typeColor) {
            $date = $start->copy()->addDays($dayOffsets[$item->weekday] ?? 0);
            $from = $this->mergeDateAndTime($date, $item->hour_from);
            $to = $this->mergeDateAndTime($date, $item->hour_to);

            return [
                'date' => $date->toDateString(),
                'interval' => $this->formatInterval($from, $to),
                'label' => $item->timeOffType?->name ?? __('attendances.time_off'),
                'color' => $typeColor[$item->time_off_type_id] ?? '#fbbf24',
                'start' => $from,
                'end' => $to,
            ];
        });

        $requestEntries = $approvedRequests->flatMap(function (TimeOffRequest $request) use ($weekStartDay, $weekEndDay, $typeColor) {
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

                $slotStart = $requestStart->greaterThan($dayStart) ? $requestStart->copy() : $dayStart;
                $slotEnd = $requestEnd->lessThan($dayEnd) ? $requestEnd->copy() : $dayEnd;

                if ($slotEnd->gt($slotStart)) {
                    $days[] = [
                        'date' => $slotStart->toDateString(),
                        'interval' => $this->formatInterval($slotStart, $slotEnd),
                        'label' => $request->type->name ?? __('attendances.time_off'),
                        'color' => $typeColor[$request->time_off_type_id] ?? '#fbbf24',
                        'start' => $slotStart,
                        'end' => $slotEnd,
                    ];
                }

                $currentDay->addDay();
            }

            return collect($days);
        });

        return $scheduledEntries
            ->concat($requestEntries)
            ->filter(fn ($entry) => ! empty($entry['date']))
            ->groupBy('date')
            ->map(fn ($items) => $items->sortBy('interval')->values());
    }

    private function formatTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('H:i');
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('H:i');
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    private function mergeDateAndTime(Carbon $date, $time): ?Carbon
    {
        if (empty($time)) {
            return null;
        }

        if ($time instanceof Carbon) {
            return $date->copy()->setTime($time->hour, $time->minute, $time->second);
        }

        if ($time instanceof \DateTimeInterface) {
            return $date->copy()->setTime(
                (int) $time->format('H'),
                (int) $time->format('i'),
                (int) $time->format('s')
            );
        }

        try {
            $parsed = Carbon::parse($time);

            return $date->copy()->setTime($parsed->hour, $parsed->minute, $parsed->second);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDate($value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        return Carbon::parse($value);
    }

    private function formatInterval(?Carbon $start, ?Carbon $end): string
    {
        if ($start && $end) {
            if ($start->isStartOfDay() && $end->isEndOfDay()) {
                return __('home.full_day');
            }

            return $start->format('H:i').' - '.$end->format('H:i');
        }

        return '';
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int|string>  $typeIds
     * @return array<int|string, string>
     */
    private function assignColors(Collection $typeIds): array
    {
        $colors = [];
        $paletteCount = count(self::TIME_OFF_COLORS);

        foreach ($typeIds->values() as $index => $typeId) {
            $colors[$typeId] = self::TIME_OFF_COLORS[$index % $paletteCount];
        }

        return $colors;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array>
     */
    public function buildEventsForUser(User $user, Carbon $periodStart, ?Carbon $periodEnd = null): Collection
    {
        $start = $periodStart->copy()->startOfWeek(Carbon::MONDAY);
        $end = $periodEnd
            ? $periodEnd->copy()->endOfWeek(Carbon::SUNDAY)
            : $start->copy()->endOfWeek(Carbon::SUNDAY);

        $plan = $this->buildForUser($user, $start, $end);
        $events = collect();

        foreach ($plan['days'] as $day) {
            $date = $day['date'] instanceof Carbon ? $day['date']->copy() : Carbon::parse($day['date']);
            $dateKey = $date->toDateString();

            if (! empty($day['holiday'])) {
                $events->push([
                    'title' => $day['holiday'],
                    'start' => $dateKey,
                    'end' => $date->copy()->addDay()->toDateString(),
                    'allDay' => true,
                    'display' => 'background',
                    'color' => 'rgba(247, 200, 48, 0.35)',
                    'borderColor' => 'transparent',
                    'textColor' => '#b45309',
                ]);
            }

            foreach ($day['presences'] ?? [] as $presence) {
                $events->push([
                    'title' => trim(($presence['type'] ?? __('home.presence')).' '.($presence['label'] ?? '')),
                    'start' => optional($presence['start'])->toIso8601String() ?? $dateKey,
                    'end' => optional($presence['end'])->toIso8601String() ?? $dateKey,
                    'allDay' => ! ($presence['start'] && $presence['end']),
                    'color' => $presence['color'] ?? '#60a5fa',
                ]);
            }

            foreach ($day['time_off'] ?? [] as $timeOff) {
                $events->push([
                    'title' => $timeOff['label'] ?? __('home.scheduled_time_off'),
                    'start' => optional($timeOff['start'])->toIso8601String() ?? $dateKey,
                    'end' => optional($timeOff['end'])->toIso8601String() ?? $dateKey,
                    'allDay' => ! ($timeOff['start'] && $timeOff['end']),
                    'color' => $timeOff['color'] ?? '#fbbf24',
                ]);
            }
        }

        return $events->values();
    }
}
