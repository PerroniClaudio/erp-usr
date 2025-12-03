<x-layouts.app>

    @vite('resources/js/daily-travel-structure.js')


    <div class="flex flex-col gap-2">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-semibold">{{ __('daily_travel.user_structure_title') }}</h1>
            </div>
            <div>
                <div class="btn btn-primary">
                    {{ __('daily_travel.save_structure') }}
                </div>

                <div class="btn btn-primary">
                    <x-lucide-arrow-left class="h-4 w-4" />
                    <a href="{{ route('users.edit', $user) }}" class="ml-2">
                        {{ __('daily_travel.user_back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <div class="flex flex-col gap-4">

        <div class="card bg-secondary/30 hover:shadow-2xl border border-secondary col-span-3">
            <div class="card-body">
                <p>{{ __('daily_travel.user_youre_editing', [
                    'user' => $user->name,
                    'company' => $company->name,
                ]) }}
                </p>
            </div>
        </div>

        <div class="card bg-base-300">
            <div class="card-body">
                <h3 class="card-title">{{ __('daily_travel.travel_data_title') }}</h3>
                <hr>
                @if ($vehicles->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('daily_travel.user_no_vehicles_associated') }}</p>
                @else
                    <form method="POST"
                        action="{{ route('admin.user.daily-trip-structure.edit-vehicle', [$user, $company]) }}"
                        class="flex flex-col gap-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="card bg-base-200">
                                <div class="card-body gap-4">
                                    <label class="form-control w-full" for="vehicle_id">
                                        <div class="label">
                                            <span class="label-text">{{ __('daily_travel.vehicle_label') }}</span>
                                        </div>
                                        <select name="vehicle_id" id="vehicle_id" class="select select-bordered w-full">
                                            <option value="">{{ __('daily_travel.select_vehicle_placeholder') }}
                                            </option>
                                            @foreach ($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}"
                                                    @php
                                                        $latestVehiclePrice = $vehicle->pricePerKmUpdates->first()?->price_per_km ?? $vehicle->price_per_km;
                                                    @endphp
                                                    data-price="{{ $latestVehiclePrice }}"
                                                    @selected($dailyTravelStructure->vehicle_id === $vehicle->id)>
                                                    {{ $vehicle->pivot->plate_number }} - {{ $vehicle->brand }}
                                                    {{ $vehicle->model }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('vehicle_id')
                                            <span class="text-error text-sm">{{ $message }}</span>
                                        @enderror
                                    </label>

                                    <label class="form-control w-full" for="cost_per_km">
                                        <div class="label">
                                            <span
                                                class="label-text">{{ __('daily_travel.vehicle_cost_per_km') }}</span>
                                        </div>
                                        @php
                                            $structureVehiclePrice = $dailyTravelStructure->vehicle?->pricePerKmUpdates->first()?->price_per_km ?? $dailyTravelStructure->vehicle?->price_per_km ?? 0;
                                            $defaultCostPerKm = $dailyTravelStructure->cost_per_km ?? $structureVehiclePrice;
                                        @endphp
                                        <input type="number" step="0.0001" min="0" name="cost_per_km"
                                            id="cost_per_km" class="input input-bordered w-full"
                                            value="{{ old('cost_per_km') ?? number_format((float) $defaultCostPerKm, 4, '.', '') }}"
                                            placeholder="{{ __('daily_travel.vehicle_cost_per_km_placeholder') }}">
                                        @error('cost_per_km')
                                            <span class="text-error text-sm">{{ $message }}</span>
                                        @enderror
                                    </label>
                                </div>
                            </div>

                            <div class="card bg-base-200">
                                <div class="card-body gap-2">
                                    <div>
                                        <p class="text-xs uppercase text-base-content/60">{{ __('daily_travel.economic_value') }}</p>
                                        <p class="text-3xl font-semibold">€ {{ number_format((float) $dailyTravelStructure->economic_value, 2, ',', '.') }}</p>
                                    </div>
                                    <p class="text-sm text-base-content/70">
                                        {{ __('daily_travel.steps_economic_value_hint') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary self-start">
                            {{ __('daily_travel.vehicle_save') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card bg-base-300">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h3 class="card-title m-0 p-0">{{ __('daily_travel.steps_title') }}</h3>
                    <button type="button" class="btn btn-sm btn-primary" id="add_step_button">
                        <x-lucide-plus class="w-4 h-4" />
                    </button>
                </div>

                <hr>

                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th class="w-12"></th>
                                <th>#</th>
                                <th>{{ __('daily_travel.steps_address') }}</th>
                                <th>{{ __('daily_travel.steps_city') }}</th>
                                <th>{{ __('daily_travel.steps_province') }}</th>
                                <th>{{ __('daily_travel.steps_zip') }}</th>
                                <th>{{ __('daily_travel.steps_time_difference') }}</th>
                                <th>{{ __('daily_travel.steps_economic_value') }}</th>
                                <th>{{ __('daily_travel.steps_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="steps_table_body"
                            data-reorder-url="{{ route('admin.user.daily-trip-structure.steps.reorder', [$user, $company]) }}">
                            @forelse ($steps as $step)
                                <tr data-step-id="{{ $step->id }}" draggable="true" class="cursor-move"
                                    data-address="{{ $step->address }}" data-city="{{ $step->city }}"
                                    data-province="{{ $step->province }}" data-zip="{{ $step->zip_code }}"
                                    data-lat="{{ $step->latitude }}" data-lng="{{ $step->longitude }}"
                                    data-time-difference="{{ $step->time_difference }}"
                                    data-economic-value="{{ (float) $step->economic_value }}"
                                    data-update-url="{{ route('admin.user.daily-trip-structure.steps.update', [$user, $company, $step]) }}"
                                    data-delete-url="{{ route('admin.user.daily-trip-structure.steps.destroy', [$user, $company, $step]) }}">
                                    <td class="text-center">
                                        <x-lucide-move class="w-4 h-4 inline" />
                                    </td>
                                    <td class="step-number">{{ $step->step_number }}</td>
                                    <td>{{ $step->address }}</td>
                                    <td>{{ $step->city }}</td>
                                    <td>{{ $step->province }}</td>
                                    <td>{{ $step->zip_code }}</td>
                                    <td>{{ $step->time_difference }}</td>
                                    <td>€ {{ number_format((float) $step->economic_value, 2, ',', '.') }}</td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button type="button"
                                                class="btn btn-sm btn-primary btn-square edit-step-button"
                                                aria-label="Modifica tappa">
                                                <x-lucide-pencil class="w-4 h-4" />
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm btn-warning btn-square delete-step-button"
                                                aria-label="Elimina tappa">
                                                <x-lucide-trash-2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-sm text-gray-500">
                                        {{ __('daily_travel.steps_empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        @if (!empty($distancesBetweenSteps))
                            <div class="p-4 bg-base-200 rounded-lg h-full space-y-3">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-semibold">{{ __('daily_travel.distance_summary_title') }}</h4>
                                </div>
                                <hr>
                                <div class="space-y-2">
                                    @foreach ($distancesBetweenSteps as $distance)
                                        <div class="p-3 rounded-lg bg-base-100 border border-base-200">
                                            <div class="text-xs uppercase text-gray-500 mb-1">
                                                {{ __('daily_travel.distance_summary_path') }}
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <div class="badge badge-outline">
                                                    {{ $distance['from']->city }} - {{ $distance['from']->address }}
                                                </div>
                                                <x-lucide-arrow-right class="w-4 h-4 text-gray-500" />
                                                <div class="badge badge-outline">
                                                    {{ $distance['to']->city }} - {{ $distance['to']->address }}
                                                </div>
                                            </div>
                                            <div class="mt-2 text-sm">
                                                <span
                                                    class="font-medium">{{ __('daily_travel.distance_summary_distance') }}:</span>
                                                {{ number_format($distance['distance'], 2) }} km
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500">
                                {{ __('daily_travel.distance_summary_empty') }}
                            </p>
                        @endif
                    </div>

                    <div class="p-4 bg-base-200 rounded-lg">
                        <h4 class="font-semibold mb-2">{{ __('daily_travel.map_title') }}</h4>
                        <hr>
                        <div id="daily-travel-map" class="mt-2 h-80 w-full rounded-lg bg-base-300"
                            data-steps='@json($mapSteps)' data-api-key="{{ $googleMapsApiKey }}">
                            <p class="p-4 text-sm text-gray-500">{{ __('daily_travel.map_placeholder') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <dialog id="add_step_modal" class="modal" data-search-url="{{ route('users.search-address') }}"
        data-store-url="{{ route('admin.user.daily-trip-structure.steps.store', [$user, $company]) }}">
        <div class="modal-box">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold">{{ __('daily_travel.steps_new_title') }}</h3>
                <form method="dialog">
                    <button class="btn btn-ghost" aria-label="{{ __('daily_travel.close') }}">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </form>
            </div>

            <hr>

            <div>
                <div class="join w-full">
                    <div class="flex-1">
                        <label class="input validator join-item w-full">
                            <input type="text" id="step-address-search-input"
                                placeholder="{{ __('daily_travel.steps_address_placeholder') }}" />
                        </label>
                    </div>
                    <button class="btn btn-primary join-item"
                        id="validate-step-address-button">{{ __('daily_travel.steps_validate') }}</button>
                </div>
            </div>

            <p class="text-error label step-error"></p>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_address') }}</legend>
                    <input type="text" name="address" class="input w-full step-form" value=""
                        placeholder="{{ __('personnel.users_address') }}" disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_street_number') }}</legend>
                    <input type="text" name="street_number" class="input w-full step-form" value=""
                        placeholder="{{ __('personnel.users_street_number') }}" disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_city') }}</legend>
                    <input type="text" name="city" class="input w-full step-form" value=""
                        placeholder="{{ __('personnel.users_city') }}" disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_postal_code') }}</legend>
                    <input type="text" name="zip_code" class="input w-full step-form" value=""
                        placeholder="{{ __('personnel.users_postal_code') }}" disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_province') }}</legend>
                    <input type="text" name="province" class="input w-full step-form" value=""
                        placeholder="{{ __('personnel.users_province') }}" disabled />
                </fieldset>

                <div></div>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_latitude') }}</legend>
                    <input type="text" name="latitude" class="input w-full step-form" value=""
                        placeholder="{{ __('personnel.users_latitude') }}" disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_longitude') }}</legend>
                    <input type="text" name="longitude" class="input w-full step-form" value=""
                        placeholder="{{ __('personnel.users_longitude') }}" disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.steps_time_difference') }}</legend>
                    <input type="number" min="0" name="time_difference" id="step_time_difference"
                        class="input w-full" value="0" />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.steps_economic_value') }}</legend>
                    <input type="number" min="0" step="0.01" name="economic_value" id="step_economic_value"
                        class="input w-full" value="0" placeholder="{{ __('daily_travel.steps_economic_value_placeholder') }}" />
                </fieldset>
            </div>




            <div class="modal-action">
                <button class="btn btn-primary" id="save-step-button">{{ __('daily_travel.steps_save') }}</button>
                <form method="dialog">
                    <button class="btn">{{ __('daily_travel.close') }}</button>
                </form>
            </div>
        </div>
    </dialog>

    <dialog id="edit_step_modal" class="modal" data-search-url="{{ route('users.search-address') }}">
        <div class="modal-box">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold">Modifica tappa</h3>
                <form method="dialog">
                    <button class="btn btn-ghost" aria-label="{{ __('daily_travel.close') }}">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </form>
            </div>

            <hr>

            <div class="mt-4">
                <div class="join w-full">
                    <div class="flex-1">
                        <label class="input validator join-item w-full">
                            <input type="text" id="edit-step-address-search-input"
                                placeholder="{{ __('daily_travel.steps_address_placeholder') }}" />
                        </label>
                    </div>
                    <button class="btn btn-primary join-item"
                        id="edit-validate-step-address-button">{{ __('daily_travel.steps_validate') }}</button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_address') }}</legend>
                    <input type="text" name="address" class="input w-full edit-step-input" data-field="address"
                        placeholder="{{ __('personnel.users_address') }}" disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_street_number') }}</legend>
                    <input type="text" name="street_number" class="input w-full edit-step-input"
                        data-field="street_number" placeholder="{{ __('personnel.users_street_number') }}"
                        disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_city') }}</legend>
                    <input type="text" name="city" class="input w-full edit-step-input" data-field="city"
                        placeholder="{{ __('personnel.users_city') }}" disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_postal_code') }}</legend>
                    <input type="text" name="zip_code" class="input w-full edit-step-input" data-field="zip_code"
                        placeholder="{{ __('personnel.users_postal_code') }}" disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_province') }}</legend>
                    <input type="text" name="province" class="input w-full edit-step-input" data-field="province"
                        placeholder="{{ __('personnel.users_province') }}" disabled />
                </fieldset>

                <div></div>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_latitude') }}</legend>
                    <input type="text" name="latitude" class="input w-full edit-step-input" data-field="latitude"
                        placeholder="{{ __('personnel.users_latitude') }}" disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_longitude') }}</legend>
                    <input type="text" name="longitude" class="input w-full edit-step-input"
                        data-field="longitude" placeholder="{{ __('personnel.users_longitude') }}" disabled />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.steps_time_difference') }}</legend>
                    <input type="number" min="0" name="time_difference" id="edit_step_time_difference"
                        class="input w-full" data-field="time_difference" value="0" />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.steps_economic_value') }}</legend>
                    <input type="number" min="0" step="0.01" name="economic_value" id="edit_step_economic_value"
                        class="input w-full" value="0" placeholder="{{ __('daily_travel.steps_economic_value_placeholder') }}" />
                </fieldset>
            </div>

            <p class="text-error text-sm mt-2" id="edit-step-error"></p>

            <div class="modal-action">
                <button class="btn btn-primary" id="save-edit-step-button">Salva modifiche</button>
                <form method="dialog">
                    <button class="btn">{{ __('daily_travel.close') }}</button>
                </form>
            </div>
        </div>
    </dialog>

    <dialog id="delete_step_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Elimina tappa</h3>
            <p class="mb-4 text-sm">Sei sicuro di voler eliminare questa tappa? L'operazione non può essere annullata.
            </p>
            <p class="text-error text-sm" id="delete-step-error"></p>
            <div class="modal-action">
                <button class="btn btn-error" id="confirm-delete-step-button">Elimina</button>
                <form method="dialog">
                    <button class="btn">{{ __('daily_travel.close') }}</button>
                </form>
            </div>
        </div>
    </dialog>
</x-layouts.app>
