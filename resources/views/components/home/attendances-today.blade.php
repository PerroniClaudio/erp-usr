@props(['usersStatus'])

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
