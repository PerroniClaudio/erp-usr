<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('time_off_requests.time_off_requests') }}</h1>
    </div>

    <hr>

    <div class="card bg-base-300">
        <div class="card-body">

            <div class="card-title">{{ __('time_off_requests.filter') }}</div>
            <hr>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('time_off_requests.filter_by_company') }}</legend>
                    <select id="company_filter" class="select w-full">
                        <option value="">{{ __('time_off_requests.all_companies') }}</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ request('company_filter') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('time_off_requests.filter_by_group') }}</legend>
                    <select id="groups_filter" class="select w-full">
                        <option value="">{{ __('time_off_requests.all_groups') }}</option>
                        @foreach ($groups as $groups)
                            <option value="{{ $groups->id }}"
                                {{ request('groups_filter') == $groups->id ? 'selected' : '' }}>
                                {{ $groups->name }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('time_off_requests.filter_by_time_off_type') }}</legend>
                    <select id="time_off_type_filter" class="select w-full">
                        <option value="">{{ __('time_off_requests.all_time_off_types') }}</option>
                        @foreach ($timeOffTypes as $time_off_type)
                            <option value="{{ $time_off_type->id }}"
                                {{ request('time_off_type_filter') == $time_off_type->id ? 'selected' : '' }}>
                                {{ $time_off_type->name }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>


            </div>

        </div>
    </div>


    <div>
        <div id="calendar" class="max-w-full"></div>
    </div>

    @push('scripts')
        @vite('resources/js/time_off_requests_admin.js')
    @endpush


</x-layouts.app>
