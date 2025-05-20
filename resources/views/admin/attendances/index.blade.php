<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('attendances.attendances_today') }}</h1>
    </div>

    <hr>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
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

</x-layouts.app>
