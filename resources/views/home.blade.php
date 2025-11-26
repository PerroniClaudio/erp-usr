<x-layouts.app>

    <!-- Componente per visualizzare gli annunci non letti -->
    <x-announcements.viewer />

    @php
        $canAccessBusinessTrips = auth()->user()?->hasRole('admin') || auth()->user()?->can('business-trips.access');
    @endphp

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
        @if ($canAccessBusinessTrips)
            <a href="{{ route('business-trips.index') }}" class="card bg-base-200 shadow-xl hover:shadow-2xl">
                <div class="card-body">
                    <x-lucide-car class="h-6 w-6 text-primary" />
                    <h2 class="card-title">{{ __('navbar.business_trips') }}</h2>
                    <p>
                        {{ __('navbar.business_trips_description') }}a
                    </p>
                </div>
            </a>
        @endif
        <a href="{{ route('time-off-requests.index') }}" class="card bg-base-200 shadow-xl hover:shadow-2xl">
            <div class="card-body">
                <x-lucide-sun class="h-6 w-6 text-primary" />
                <h2 class="card-title">{{ __('navbar.time_off') }}</h2>
                <p>
                    {{ __('navbar.time_off_description') }}
                </p>
            </div>
        </a>
        <a href="{{ route('standard.profile.edit') }}" class="card bg-base-200 shadow-xl hover:shadow-2xl">
            <div class="card-body">
                <x-lucide-circle-user class="h-6 w-6 text-primary" />
                <h2 class="card-title">{{ __('navbar.profile') }}</h2>
                <p>
                    {{ __('navbar.profile_description') }}
                </p>
            </div>
        </a>

        @unless ($failedAttendances->isEmpty())
            <div class="card bg-base-200 shadow-xl hover:shadow-2xl md:col-span-3">
                <div class="card-body">
                    <h3 class="card-title">{{ __('attendances.failed_attendances') }}</h3>
                    <hr>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($failedAttendances as $attendance)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('failed-attendances.justify', $attendance) }}"
                                            class="btn btn-primary">
                                            {{ __('attendances.justify') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endunless

    </div>


</x-layouts.app>
