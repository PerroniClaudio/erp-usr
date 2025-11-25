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
                <h3 class="card-title">Dati viaggio</h3>
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
                                                    data-price="{{ $vehicle->price_per_km }}"
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
                                        <input type="number" step="0.01" min="0" name="cost_per_km"
                                            id="cost_per_km" class="input input-bordered w-full"
                                            value="{{ old('cost_per_km') ?? number_format($dailyTravelStructure->cost_per_km ?? ($dailyTravelStructure->vehicle->price_per_km ?? 0), 2, '.', '') }}"
                                            placeholder="{{ __('daily_travel.vehicle_cost_per_km_placeholder') }}">
                                        @error('cost_per_km')
                                            <span class="text-error text-sm">{{ $message }}</span>
                                        @enderror
                                    </label>
                                </div>
                            </div>

                            <div class="card bg-base-200">
                                <div class="card-body grid grid-cols-1 gap-4">
                                    <label class="form-control w-full" for="economic_value">
                                        <div class="label">
                                            <span class="label-text">{{ __('daily_travel.economic_value') }}</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" name="economic_value"
                                            id="economic_value" class="input input-bordered w-full"
                                            value="{{ old('economic_value', number_format($dailyTravelStructure->economic_value ?? 0, 2, '.', '')) }}"
                                            placeholder="{{ __('daily_travel.economic_value_placeholder') }}">
                                        @error('economic_value')
                                            <span class="text-error text-sm">{{ $message }}</span>
                                        @enderror
                                    </label>

                                    <label class="form-control w-full" for="travel_minutes">
                                        <div class="label">
                                            <span class="label-text">{{ __('daily_travel.travel_minutes') }}</span>
                                        </div>
                                        <input type="number" min="0" name="travel_minutes" id="travel_minutes"
                                            class="input input-bordered w-full"
                                            value="{{ old('travel_minutes', $dailyTravelStructure->travel_minutes ?? 0) }}"
                                            placeholder="{{ __('daily_travel.travel_minutes_placeholder') }}">
                                        @error('travel_minutes')
                                            <span class="text-error text-sm">{{ $message }}</span>
                                        @enderror
                                    </label>
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
                    <h3 class="card-title m-0 p-0">Tappe</h3>
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
                                <th>Indirizzo</th>
                                <th>Città</th>
                                <th>Provincia</th>
                                <th>CAP</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="steps_table_body"
                            data-reorder-url="{{ route('admin.user.daily-trip-structure.steps.reorder', [$user, $company]) }}">
                            @forelse ($steps as $step)
                                <tr data-step-id="{{ $step->id }}" draggable="true" class="cursor-move">
                                    <td class="text-center">
                                        <x-lucide-move class="w-4 h-4 inline" />
                                    </td>
                                    <td class="step-number">{{ $step->step_number }}</td>
                                    <td>{{ $step->address }}</td>
                                    <td>{{ $step->city }}</td>
                                    <td>{{ $step->province }}</td>
                                    <td>{{ $step->zip_code }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-error">
                                            <x-lucide-trash-2 class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-sm text-gray-500">Nessuna tappa
                                        disponibile.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <dialog id="add_step_modal" class="modal" data-search-url="{{ route('users.search-address') }}"
        data-store-url="{{ route('admin.user.daily-trip-structure.steps.store', [$user, $company]) }}">
        <div class="modal-box">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold">Nuova tappa</h3>
                <form method="dialog">
                    <button class="btn btn-ghost" aria-label="Chiudi">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </form>
            </div>

            <hr>

            <div>
                <div class="join w-full">
                    <div class="flex-1">
                        <label class="input validator join-item w-full">
                            <input type="text" id="step-address-search-input" placeholder="Via Garibaldi 22" />
                        </label>
                    </div>
                    <button class="btn btn-primary join-item" id="validate-step-address-button">Convalida</button>
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
            </div>




            <div class="modal-action">
                <button class="btn btn-primary" id="save-step-button">Salva</button>
                <form method="dialog">
                    <button class="btn">Chiudi</button>
                </form>
            </div>
        </div>
    </dialog>
</x-layouts.app>
