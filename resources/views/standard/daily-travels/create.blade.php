<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('daily_travel.create_title') }}</h1>
        <button class="btn btn-primary" type="button" onclick="document.getElementById('submit-button').click()">
            {{ __('daily_travel.save_daily_travel') }}
        </button>
    </div>

    <hr>

    <div class="flex flex-col gap-4">
        <div class="card bg-base-300">
            <form class="card-body space-y-4" method="POST" action="{{ route('daily-travels.store') }}">
                @csrf

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.company_label') }}</legend>
                    <select id="company_id" class="select" name="company_id"
                        value="{{ old('company_id', $selectedCompanyId) }}">
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected(old('company_id', $selectedCompanyId) == $company->id)>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <p class="text-sm text-error mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.travel_date') }}</legend>
                    <input type="date" name="travel_date" class="input"
                        value="{{ old('travel_date', \Carbon\Carbon::today()->toDateString()) }}" />
                    @error('travel_date')
                        <p class="text-sm text-error mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <button id="submit-button" type="submit" class="hidden">
                    {{ __('daily_travel.save_daily_travel') }}
                </button>
            </form>
        </div>

        <div class="card bg-base-200">
            <div class="card-body" data-structure-preview
                data-selected-company="{{ old('company_id', $selectedCompanyId) }}"
                data-structures='@json($structuresMap)'
                data-missing-message="{{ __('daily_travel.preview_missing') }}"
                data-vehicle-label="{{ __('daily_travel.preview_vehicle') }}"
                data-vehicle-none="{{ __('daily_travel.preview_vehicle_none') }}"
                data-cost-per-km-label="{{ __('daily_travel.preview_cost_per_km') }}"
                data-economic-value-label="{{ __('daily_travel.preview_economic_value') }}"
                data-steps-title="{{ __('daily_travel.preview_steps_title') }}"
                data-steps-empty="{{ __('daily_travel.preview_steps_empty') }}"
                data-step-label="{{ __('daily_travel.preview_step_label', ['number' => ':number']) }}"
                data-distance-title="{{ __('daily_travel.distance_summary_title') }}"
                data-distance-path="{{ __('daily_travel.distance_summary_path') }}"
                data-distance-distance="{{ __('daily_travel.distance_summary_distance') }}"
                data-distance-empty="{{ __('daily_travel.distance_summary_empty') }}"
                data-map-placeholder="{{ __('daily_travel.map_placeholder') }}"
                data-google-api-key="{{ $googleMapsApiKey }}" data-currency-symbol="€">
                <div class="flex items-center justify-between">
                    <h3 class="card-title m-0">{{ __('daily_travel.preview_title') }}</h3>
                    <span class="badge badge-outline">{{ __('daily_travel.preview_read_only') }}</span>
                </div>

                <div data-preview-meta class="">
                    {{-- Populated by JS --}}
                </div>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4 mt-4">
        <div class="card bg-base-200">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold">{{ __('daily_travel.preview_steps_title') }}</h4>
                    <span class="badge badge-outline">{{ __('daily_travel.preview_read_only') }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th class="w-12">#</th>
                                <th>{{ __('daily_travel.steps_address') }}</th>
                                <th>{{ __('daily_travel.steps_city') }}</th>
                                <th>{{ __('daily_travel.steps_province') }}</th>
                                <th>{{ __('daily_travel.steps_zip') }}</th>
                            </tr>
                        </thead>
                        <tbody data-steps-table>
                            {{-- Populated by JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card bg-base-200">
            <div class="card-body space-y-4">
                <h4 class="font-semibold">{{ __('daily_travel.distance_summary_title') }}</h4>
                <hr>
                <div class="space-y-2" data-distance-summary></div>

                <div>
                    <h4 class="font-semibold mb-2">{{ __('daily_travel.map_title') }}</h4>
                    <hr>
                    <div id="daily-travel-map"
                        class="mt-2 h-80 w-full rounded-lg bg-base-300 flex items-center justify-center">
                        <p class="text-sm text-base-content/70">{{ __('daily_travel.map_placeholder') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/daily-travel-create.js')
    @endpush
</x-layouts.app>
