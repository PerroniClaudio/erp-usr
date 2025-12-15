<x-layouts.app>
    <x-layouts.header :title="__('attendances.attendances')">
        <x-slot:actions>
            <div class="flex gap-2">
                <a href="{{ route('admin.attendances.create') }}" class="btn btn-primary">
                    {{ __('attendances.new_attendance') }}
                </a>
            </div>
        </x-slot:actions>
    </x-layouts.header>

    <div tabindex="0" class="collapse collapse-arrow bg-base-200 border-base-300 border">
        <div class="collapse-title font-semibold">{{ __('attendances.attendances_today') }}</div>
        <div class="collapse-content">
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


    <div class="card bg-base-300">
        <div class="card-body">

            <div class="card-title">{{ __('attendances.filter') }}</div>
            <hr>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('attendances.filter_by_company') }}</legend>
                    <select id="company_filter" class="select w-full">
                        <option value="">{{ __('attendances.all_companies') }}</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ request('company_filter') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('attendances.filter_by_group') }}</legend>
                    <select id="groups_filter" class="select w-full">
                        <option value="">{{ __('attendances.all_groups') }}</option>
                        @foreach ($groups as $groups)
                            <option value="{{ $groups->id }}"
                                {{ request('groups_filter') == $groups->id ? 'selected' : '' }}>
                                {{ $groups->name }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset hidden">
                    <legend class="fieldset-legend">{{ __('attendances.filter_by_user') }}</legend>
                    <select id="time_off_type_filter" class="select w-full">
                        <option value="">{{ __('attendances.all_users') }}</option>

                    </select>
                </fieldset>

            </div>

        </div>
    </div>


    <div>
        <div id="calendar" class="max-w-full"></div>
    </div>

    @push('scripts')
        @vite('resources/js/attendances_admin.js')
    @endpush

</x-layouts.app>
