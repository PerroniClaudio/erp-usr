<x-layouts.app>

    <div class="flex flex-col gap-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
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
    </div>


</x-layouts.app>
