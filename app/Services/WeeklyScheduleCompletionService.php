<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSchedule;
use App\Models\WeeklyScheduleCompletion;
use Carbon\Carbon;

class WeeklyScheduleCompletionService
{
    public function refreshForWeek(Carbon $weekStart): WeeklyScheduleCompletion
    {
        $weekStart = $weekStart->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $nonAdminUserIds = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->pluck('id');

        $totalUsers = $nonAdminUserIds->count();

        $completedUsers = 0;
        if ($totalUsers > 0) {
            $completedUsers = UserSchedule::whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->whereIn('user_id', $nonAdminUserIds)
                ->distinct('user_id')
                ->count('user_id');
        }

        $completion = WeeklyScheduleCompletion::firstOrNew(
            ['week_start' => $weekStart->toDateString()],
            ['week_end' => $weekEnd->toDateString()]
        );

        $completion->week_end = $weekEnd->toDateString();
        $completion->total_users = $totalUsers;
        $completion->completed_users = $completedUsers;
        $completion->completed_at = ($totalUsers > 0 && $completedUsers >= $totalUsers) ? now() : null;
        $completion->save();

        return $completion;
    }

    public function isWeekCompleted(Carbon $weekStart): bool
    {
        $weekStart = $weekStart->copy()->startOfWeek(Carbon::MONDAY);
        $record = WeeklyScheduleCompletion::forWeek($weekStart)->first();

        return (bool) ($record?->completed_at);
    }
}
