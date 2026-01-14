<x-layouts.app>

    <div data-homepage class="flex flex-col gap-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @if (!empty($approvalPending) && $approvalPending)
                <a href="{{ route('user-schedules.index', ['week_start' => $weekStart->toDateString()]) }}"
                    class="card group bg-primary/15 hover:shadow-2xl border border-primary/50 md:col-span-2 lg:col-span-3 relative overflow-hidden transition duration-300 hover:-translate-y-0.5 hover:border-primary"
                    data-home-card>
                    <div class="absolute inset-0 bg-gradient-to-br from-primary/20 via-transparent to-primary/5 opacity-0 transition duration-300 group-hover:opacity-100"></div>
                    <div class="card-body relative z-10">
                        <x-lucide-calendar-check class="h-6 w-6 text-primary" />
                        <h2 class="card-title">{{ __('personnel.users_schedule_approval_title') }}</h2>
                        <p>{{ __('personnel.users_schedule_approval_desc', ['date' => $weekStart->format('d/m/Y')]) }}
                        </p>
                    </div>
                </a>
            @endif

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('admin.attendances.index') }}"
                        class="card bg-base-200/80 border border-base-300/60 hover:shadow-2xl transition duration-300 hover:-translate-y-0.5 hover:border-primary/40"
                        data-home-card>
                        <div class="card-body">
                            <x-lucide-calendar class="h-6 w-6 text-primary" />
                            <h2 class="card-title">{{ __('navbar.attendances') }}</h2>
                            <p>
                                {{ __('navbar.attendances_description') }}
                            </p>
                        </div>
                    </a>
                    <a href="{{ route('business-trips.index') }}"
                        class="card bg-base-200/80 border border-base-300/60 hover:shadow-2xl transition duration-300 hover:-translate-y-0.5 hover:border-primary/40"
                        data-home-card>
                        <div class="card-body">
                            <x-lucide-car class="h-6 w-6 text-primary" />
                            <h2 class="card-title">{{ __('navbar.business_trips') }}</h2>
                            <p>
                                {{ __('navbar.business_trips_description') }}
                            </p>
                        </div>
                    </a>
                    <a href="{{ route('admin.time-off.index') }}"
                        class="card bg-base-200/80 border border-base-300/60 hover:shadow-2xl transition duration-300 hover:-translate-y-0.5 hover:border-primary/40"
                        data-home-card>
                        <div class="card-body">
                            <x-lucide-sun class="h-6 w-6 text-primary" />
                            <h2 class="card-title">{{ __('navbar.time_off') }}</h2>
                            <p>
                                {{ __('navbar.time_off_description') }}
                            </p>
                        </div>
                    </a>
                    <a href="{{ route('weekly-scheduled-timeoff.index') }}"
                        class="card bg-base-200/80 border border-base-300/60 hover:shadow-2xl transition duration-300 hover:-translate-y-0.5 hover:border-primary/40"
                        data-home-card>
                        <div class="card-body">
                            <x-lucide-sun-moon class="h-6 w-6 text-primary" />
                            <h2 class="card-title">{{ __('navbar.weekly_scheduled_time_off') }}</h2>
                            <p>
                                {{ __('navbar.weekly_scheduled_time_off_description') }}
                            </p>
                        </div>
                    </a>
                </div>
                <div data-home-panel>
                    <x-home.attendances-today :usersStatus="$usersStatus" />
                </div>
            </div>
            <div class="flex flex-col gap-4">
                <div data-home-panel>
                    <x-home.pending-time-off-requests :pendingTimeOffRequests="$pendingTimeOffRequests" />
                </div>
                <div data-home-panel>
                    <x-home.failed-attendances-requests :failedAttendancesRequests="$failedAttendancesRequests" />
                </div>
                <div data-home-panel>
                    <x-home.pending-overtime-requests :pendingOvertimeRequests="$pendingOvertimeRequests" />
                </div>
                <div data-home-panel>
                    <x-home.pending-schedule-change-requests :pendingScheduleRequests="$pendingScheduleRequests" />
                </div>
            </div>
        </div>
    </div>


</x-layouts.app>
