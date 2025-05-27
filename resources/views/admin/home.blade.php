<x-layouts.app>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

        <div class="card bg-base-200 col-span-3">
            <div class="card-body">
                <div class="card-title">{{ __('attendances.attendances_today') }}</div>
                <hr>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Presenza registrata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usersStatus as $status)
                            <tr>
                                <td>{{ $status['user']['name'] }}</td>
                                <td>
                                    @switch($status['status'])
                                        @case('registered')
                                            <div role="alert" class="alert alert-success alert-soft lg:w-1/3">
                                                <x-lucide-check-circle class="h-6 w-6" />
                                                <span>{{ __('attendances.registered') }}</span>
                                            </div>
                                        @break

                                        @case('time_off')
                                            <div role="alert" class="alert alert-secondary alert-soft lg:w-1/3">
                                                <x-lucide-sun class="h-6 w-6" />
                                                <span>{{ __('attendances.time_off') }}</span>
                                            </div>
                                        @break

                                        @case('not_registered')
                                            <div role="alert" class="alert alert-error alert-soft lg:w-1/3">
                                                <x-lucide-alert-triangle class="h-6 w-6" />
                                                <span>{{ __('attendances.not_registered') }}</span>
                                            </div>
                                        @break
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card bg-base-200 col-span-3">
            <div class="card-body">
                <div class="card-title">{{ __('time_off_requests.pending_requests') }}</div>
                <hr>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Data</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @unless ($pendingTimeOffRequests->count())
                            <tr>
                                <td colspan="3" class="text-center">
                                    {{ __('time_off_requests.no_pending_requests') }}
                                </td>
                            </tr>
                        @endunless

                        @foreach ($pendingTimeOffRequests as $request)
                            <tr>
                                <td>{{ $request['title'] }}</td>
                                <td>{{ $request['type'] }}</td>
                                <td>{{ $request['start_end'] }}</td>
                                <td>
                                    <a href="{{ route('admin.time-off.edit', $request['batch']) }}"
                                        class="btn btn-primary btn-sm">{{ __('time_off_requests.handle') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>


    </div>


</x-layouts.app>
