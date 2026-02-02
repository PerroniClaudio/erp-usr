<x-layouts.app>
    <x-layouts.header :title="__('daily_travel.create_title')">
        <x-slot:actions>
            <button class="btn btn-primary" type="button" onclick="document.getElementById('submit-button').click()">
                {{ __('daily_travel.save_daily_travel') }}
            </button>
        </x-slot:actions>
    </x-layouts.header>

    <div class="flex flex-col gap-4">
        <div class="card bg-base-300">
            <form class="card-body space-y-4" method="POST" action="{{ route('daily-travels.store') }}">
                @csrf

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.travel_date') }}</legend>
                    <input type="date" name="travel_date" class="input"
                        value="{{ old('travel_date', \Carbon\Carbon::today()->toDateString()) }}" />
                    @error('travel_date')
                        <p class="text-sm text-error mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <fieldset class="fieldset space-y-3">
                    <legend class="fieldset-legend">{{ __('daily_travel.route_title') }}</legend>
                    <p class="text-sm text-base-content/70">
                        {{ __('daily_travel.route_start_note') }}
                    </p>

                    @if (!$userHeadquarter)
                        <div class="alert alert-warning text-sm">
                            {{ __('daily_travel.route_missing_user_headquarter') }}
                        </div>
                    @endif

                    <div class="space-y-2" data-intermediate-list>
                        {{-- Populated by JS --}}
                    </div>

                    <button type="button" class="btn btn-primary" id="open_intermediate_modal">
                        {{ __('daily_travel.route_add_intermediate') }}
                    </button>
                    @error('intermediate_headquarter_ids')
                        <p class="text-sm text-error mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>
                @error('structure')
                    <p class="text-sm text-error mt-1">{{ $message }}</p>
                @enderror

                <button id="submit-button" type="submit" class="hidden">
                    {{ __('daily_travel.save_daily_travel') }}
                </button>
            </form>
        </div>

        <dialog id="intermediate_modal" class="modal">
            <div class="modal-box">
                <div class="flex flex-row-reverse items-end">
                    <form method="dialog">
                        <button class="btn btn-ghost">
                            <x-lucide-x class="w-4 h-4" />
                        </button>
                    </form>
                </div>
                <h3 class="text-2xl font-semibold mb-4">{{ __('daily_travel.route_add_intermediate') }}</h3>
                <div class="space-y-3">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('daily_travel.company_label') }}</legend>
                        <select id="intermediate_company_id" class="select select-bordered w-full">
                            <option value="">{{ __('daily_travel.company_placeholder') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('daily_travel.route_intermediate_label') }}</legend>
                        <select id="intermediate_headquarter_id" class="select select-bordered w-full">
                            <option value="">{{ __('daily_travel.route_intermediate_none') }}</option>
                        </select>
                    </fieldset>
                </div>

                <div class="mt-4 flex flex-col sm:flex-row gap-2">
                    <button type="button" class="btn btn-primary w-full sm:w-auto" id="add_intermediate_button">
                        {{ __('daily_travel.route_add_intermediate') }}
                    </button>
                    <form method="dialog" class="w-full sm:w-auto">
                        <button type="submit" class="btn btn-ghost w-full sm:w-auto">
                            {{ __('daily_travel.close') }}
                        </button>
                    </form>
                </div>
            </div>
        </dialog>

        <div class="card bg-base-200">
            <div class="card-body" data-structure-preview
                data-selected-company="{{ $selectedCompanyId }}"
                data-selected-start-location="{{ \App\Models\DailyTravelStructure::START_LOCATION_OFFICE }}"
                data-structures='@json($structuresMap)' data-headquarters='@json($headquartersMap)'
                data-user-headquarter='@json($userHeadquarter)'
                data-missing-message="{{ __('daily_travel.preview_missing') }}"
                data-vehicle-label="{{ __('daily_travel.preview_vehicle') }}"
                data-vehicle-none="{{ __('daily_travel.preview_vehicle_none') }}"
                data-cost-per-km-label="{{ __('daily_travel.preview_cost_per_km') }}"
                data-economic-value-label="{{ __('daily_travel.preview_economic_value') }}"
                data-start-location-label="{{ __('daily_travel.start_location_label') }}"
                data-start-location-office-label="{{ __('daily_travel.start_location_office') }}"
                data-start-location-value="{{ \App\Models\DailyTravelStructure::START_LOCATION_OFFICE }}"
                data-route-title="{{ __('daily_travel.route_title') }}"
                data-route-start-label="{{ __('daily_travel.route_start_end') }}"
                data-route-empty="{{ __('daily_travel.route_intermediate_list_empty') }}"
                data-route-none="{{ __('daily_travel.route_intermediate_none') }}"
                data-route-missing-headquarter="{{ __('daily_travel.route_missing_user_headquarter') }}"
                data-steps-title="{{ __('daily_travel.preview_steps_title') }}"
                data-steps-empty="{{ __('daily_travel.preview_steps_empty') }}"
                data-step-label="{{ __('daily_travel.preview_step_label', ['number' => ':number']) }}"
                data-distance-title="{{ __('daily_travel.distance_summary_title') }}"
                data-distance-path="{{ __('daily_travel.distance_summary_path') }}"
                data-distance-distance="{{ __('daily_travel.distance_summary_distance') }}"
                data-distance-empty="{{ __('daily_travel.distance_summary_empty') }}"
                data-map-placeholder="{{ __('daily_travel.map_placeholder') }}"
                data-mapbox-token="{{ $mapboxAccessToken }}" data-currency-symbol="€">
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
