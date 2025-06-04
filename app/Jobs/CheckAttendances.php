<?php

namespace App\Jobs;

use App\Models\FailedAttendance;
use App\Models\AttendanceType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckAttendances implements ShouldQueue {
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct() {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        //

        Log::info('Checking attendances for all users');
        $usersWithAnomalies = [];
        $messages = [];
        $users = User::whereHas('attendances')
            ->orWhereHas('timeOffRequests')
            ->get();

        foreach ($users as $user) {
            $today = date('Y-m-d');

            $totalHours = 0;

            $allowedTypes = AttendanceType::whereIn('acronym', [
                'LS',
                'LC',
                'SW',
                'MA'
            ])->pluck('id')->toArray();

            $attendances = $user->attendances()
                ->whereDate('date', $today)
                ->whereIn('attendance_type_id', $allowedTypes)
                ->get();

            foreach ($attendances as $attendance) {
                $total = Carbon::parse($attendance->date . ' ' . $attendance->time_in)
                    ->diffInHours(Carbon::parse($attendance->date . ' ' . $attendance->time_out));

                $totalHours += $total;
            }

            $timeOffRequests = $user->timeOffRequests()->whereLike('date_from', $today)->get();

            foreach ($timeOffRequests as $request) {
                $total = Carbon::parse($request->date_from)
                    ->diffInHours(Carbon::parse($request->date_to));

                $totalHours += $total;
            }


            if ($totalHours < 8) {
                $messages[] = "User ID {$user->id} has less than 8 hours of attendance for {$today}";
                $lackingHours = 8 - $totalHours;

                $usersWithAnomalies[] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'total_hours' => $totalHours,
                    'date' => $today,
                ];

                FailedAttendance::create([
                    'user_id' => $user->id,
                    'date' => $today,
                    'status' => 0,
                    'reason' => "Giustifica le $lackingHours ore mancanti...",
                    'requested_type' => 0,
                    'requested_hours' => $lackingHours,
                ]);

                // Send notification or email to the user or admin
            } else {
                $messages[] = "User ID {$user->id} total hours for {$today}: {$totalHours}";
            }
        }

        if (env('APP_ENV') === 'production') {
            Mail::to(env('ATTENDANCES_EMAIL'))
                ->send(new \App\Mail\AttendanceAnomaliesReport($usersWithAnomalies));
        } else {
            Mail::to(env('DEV_EMAIL'))
                ->send(new \App\Mail\AttendanceAnomaliesReport($usersWithAnomalies));
        }


        Log::info('Attendance check completed', [
            'messages' => $messages,
            'users_with_anomalies' => $usersWithAnomalies,
        ]);
    }
}
