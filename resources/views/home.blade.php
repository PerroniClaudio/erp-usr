<x-layouts.app>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('attendances.index') }}" class="card bg-base-200 shadow-xl hover:shadow-2xl">
            <div class="card-body">
                <x-lucide-calendar class="h-6 w-6 text-primary" />
                <h2 class="card-title">{{ __('navbar.attendances') }}</h2>
                <p>
                    {{ __('navbar.attendances_description') }}
                </p>
            </div>
        </a>
        <a href="{{ route('business-trips.index') }}" class="card bg-base-200 shadow-xl hover:shadow-2xl">
            <div class="card-body">
                <x-lucide-car class="h-6 w-6 text-primary" />
                <h2 class="card-title">{{ __('navbar.business_trips') }}</h2>
                <p>
                    {{ __('navbar.business_trips_description') }}a
                </p>
            </div>
        </a>
        <a href="{{ route('time-off-requests.index') }}" class="card bg-base-200 shadow-xl hover:shadow-2xl">
            <div class="card-body">
                <x-lucide-sun class="h-6 w-6 text-primary" />
                <h2 class="card-title">{{ __('navbar.time_off') }}</h2>
                <p>
                    {{ __('navbar.time_off_description') }}
                </p>
            </div>
        </a>
    </div>


</x-layouts.app>
