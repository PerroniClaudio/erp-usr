<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('personnel.users_vehicles_edit') }}</h1>
        <div class="btn btn-primary hidden lg:inline-flex" onclick="document.getElementById('submit-button').click()">
            {{ __('personnel.users_vehicles_save') }}
        </div>
    </div>

    <hr>

    <div class="card bg-base-300 ">
        <div class="card-body">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_vehicles_search') }}</legend>

                    <div class="join w-full">
                        <div class="flex-1">
                            <label class="input validator join-item w-full p-0">
                                <input type="text" name="search" id="search-vehicle" class="input w-full"
                                    placeholder="{{ __('personnel.users_vehicles_search_placeholder') }}" />
                            </label>
                        </div>
                        <button class="btn btn-primary join-item" id="search-vehicle-button">Cerca</button>
                    </div>
                </fieldset>

                <fieldset class="fieldset" id="vehicle-selector-container">
                    <legend class="fieldset-legend">{{ __('personnel.users_vehicles_model') }}</legend>
                    <select class="select w-full " name="model" id="model" disabled>
                        <option value="{{ $joinedVehicle->id }}" selected>{{ $joinedVehicle->model }}</option>

                    </select>
                </fieldset>
            </div>

            <form action="{{ route('users.vehicles.update', [$user, $joinedVehicle]) }}" method="POST"
                id="vehicle-form">
                @csrf
                @method('POST')

                <input type="hidden" name="vehicle_id" id="vehicle_id" value="{{ old('vehicle_id', $vehicle->id) }}">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_plate_number') }}</legend>
                        <input type="text" name="plate_number" id="plate_number" class="input w-full"
                            placeholder="{{ __('personnel.users_vehicles_plate_number_placeholder') }}"
                            value="{{ old('plate_number', $joinedVehicle->pivot->plate_number) }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_type') }}</legend>
                        <select class="select w-full" name="vehicle_type" id="vehicle_type">
                            <option value="" disabled
                                {{ old('vehicle_type', $joinedVehicle->pivot->vehicle_type) ? '' : 'selected' }}>
                                {{ __('personnel.users_vehicles_select_type') }}
                            </option>
                            @foreach ($vehicleTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('vehicle_type', $joinedVehicle->pivot->vehicle_type) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_ownership_type') }}</legend>
                        <select class="select w-full" name="ownership_type" id="ownership_type">
                            <option value="" disabled
                                {{ old('ownership_type', $joinedVehicle->pivot->ownership_type) ? '' : 'selected' }}>
                                {{ __('personnel.users_vehicles_select_ownership_type') }}
                            </option>
                            @foreach ($ownershipTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('ownership_type', $joinedVehicle->pivot->ownership_type) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_purchase_type') }}</legend>
                        <select class="select w-full" name="purchase_type" id="purchase_type">
                            <option value="" disabled
                                {{ old('purchase_type', $joinedVehicle->pivot->purchase_type) ? '' : 'selected' }}>
                                {{ __('personnel.users_vehicles_select_purchase_type') }}
                            </option>
                            @foreach ($purchaseTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('purchase_type', $joinedVehicle->pivot->purchase_type) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_contract_start_date') }}
                        </legend>
                        <input type="date" name="contract_start_date" id="contract_start_date" class="input w-full"
                            value="{{ old('contract_start_date', $joinedVehicle->pivot->contract_start_date) }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_contract_end_date') }}</legend>
                        <input type="date" name="contract_end_date" id="contract_end_date" class="input w-full"
                            value="{{ old('contract_end_date', $joinedVehicle->pivot->contract_end_date) }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_mileage') }}</legend>
                        <input type="number" name="mileage" id="mileage" class="input w-full"
                            placeholder="{{ __('personnel.users_vehicles_mileage_placeholder') }}"
                            value="{{ old('mileage', $joinedVehicle->pivot->mileage) }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_mileage_update_date') }}
                        </legend>
                        <input type="date" name="mileage_update_date" id="mileage_update_date" class="input w-full"
                            value="{{ old('mileage_update_date', $joinedVehicle->pivot->mileage_update_date) }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_price_per_km') }}</legend>
                        <input type="number" step="0.0001" min="0" name="price_per_km" id="price_per_km"
                            class="input w-full"
                            placeholder="{{ __('personnel.users_vehicles_price_per_km_placeholder') }}"
                            value="{{ old('price_per_km', number_format($vehicle->price_per_km ?? 0, 4, '.', '')) }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_price_per_km_update_date') }}
                        </legend>
                        <input type="date" name="price_per_km_update_date" id="price_per_km_update_date"
                            class="input w-full"
                            value="{{ old('price_per_km_update_date', optional($vehicle->last_update)->toDateString()) }}" />
                    </fieldset>

                    <button type="submit" class="lg:hidden btn btn-primary w-full"
                        id="submit-button">{{ __('personnel.users_vehicles_save') }}</button>
                </div>
            </form>

        </div>
    </div>

    <div class="card bg-base-300">
        <div class="card-body">

            <h2 class="card-title">{{ __('personnel.users_vehicles_mileage_history') }}</h2>


            <hr>

            <table class="table w-full">
                <thead>
                    <tr>
                        <th>{{ __('personnel.users_vehicles_mileage_history_date') }}</th>
                        <th>{{ __('personnel.users_vehicles_mileage_history_mileage') }}</th>
                    </tr>
                </thead>
                <tbody id="mileage-history">
                    @unless ($joinedVehicle->mileageUpdates->count())
                        <tr>
                            <td colspan="2" class="text-center">
                                {{ __('personnel.users_vehicles_mileage_history_no_updates') }}
                            </td>
                        </tr>
                    @endunless
                    @foreach ($mileageUpdates as $history)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($history->update_date)->format('d/m/Y') }}</td>
                            <td>{{ $history->mileage }}</td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

        </div>
    </div>

    <div class="card bg-base-300">
        <div class="card-body">

            <h2 class="card-title">{{ __('personnel.users_vehicles_price_per_km_history') }}</h2>

            <hr>

            <table class="table w-full">
                <thead>
                    <tr>
                        <th>{{ __('personnel.users_vehicles_price_per_km_history_date') }}</th>
                        <th>{{ __('personnel.users_vehicles_price_per_km_history_value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @unless ($pricePerKmUpdates->count())
                        <tr>
                            <td colspan="2" class="text-center">
                                {{ __('personnel.users_vehicles_price_per_km_history_no_updates') }}
                            </td>
                        </tr>
                    @endunless
                    @foreach ($pricePerKmUpdates as $history)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($history->update_date)->format('d/m/Y') }}</td>
                            <td>{{ number_format($history->price_per_km, 4, ',', '') }}</td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

        </div>
    </div>


    @push('scripts')
        @vite('resources/js/vehicles.js')
    @endpush

</x-layouts.app>
