<x-layouts.app>

    <div class="flex flex-col gap-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            @if (!empty($approvalPending) && $approvalPending)
                <a href="{{ route('user-schedules.index', ['week_start' => $weekStart->toDateString()]) }}"
                    class="card bg-warning/30 hover:shadow-2xl border border-warning hidden">
                    <div class="card-body">
                        <x-lucide-calendar-check class="h-6 w-6 text-warning" />
                        <h2 class="card-title">{{ __('personnel.users_schedule_approval_title') }}</h2>
                        <p>{{ __('personnel.users_schedule_approval_desc', ['date' => $weekStart->format('d/m/Y')]) }}
                        </p>
                    </div>
                </a>
            @endif
            <a href="{{ route('admin.attendances.index') }}" class="card bg-base-200 hover:shadow-2xl">
                <div class="card-body">
                    <x-lucide-calendar class="h-6 w-6 text-primary" />
                    <h2 class="card-title">{{ __('navbar.attendances') }}</h2>
                    <p>
                        {{ __('navbar.attendances_description') }}
                    </p>
                </div>
            </a>
            <a href="{{ route('business-trips.index') }}" class="card bg-base-200 hover:shadow-2xl">
                <div class="card-body">
                    <x-lucide-car class="h-6 w-6 text-primary" />
                    <h2 class="card-title">{{ __('navbar.business_trips') }}</h2>
                    <p>
                        {{ __('navbar.business_trips_description') }}
                    </p>
                </div>
            </a>
            <a href="{{ route('admin.time-off.index') }}" class="card bg-base-200 hover:shadow-2xl">
                <div class="card-body">
                    <x-lucide-sun class="h-6 w-6 text-primary" />
                    <h2 class="card-title">{{ __('navbar.time_off') }}</h2>
                    <p>
                        {{ __('navbar.time_off_description') }}
                    </p>
                </div>
            </a>
        </div>

        <x-home.attendances-today :usersStatus="$usersStatus" />
        <x-home.pending-time-off-requests :pendingTimeOffRequests="$pendingTimeOffRequests" />
        <x-home.failed-attendances-requests :failedAttendancesRequests="$failedAttendancesRequests" />
        <x-home.pending-overtime-requests :pendingOvertimeRequests="$pendingOvertimeRequests" />
    </div>


</x-layouts.app>
