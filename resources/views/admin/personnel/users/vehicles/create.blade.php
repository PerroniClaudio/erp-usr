<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('personnel.users_vehicles_add') }}</h1>
        <div class="btn btn-primary hidden lg:inline-flex" onclick="document.getElementById('submit-button').click()">
            {{ __('personnel.users_vehicles_add') }}
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

                <fieldset class="fieldset hidden" id="vehicle-selector-container">
                    <legend class="fieldset-legend">{{ __('personnel.users_vehicles_model') }}</legend>
                    <select class="select w-full " name="model" id="model" disabled>
                        <option value="" disabled selected>{{ __('personnel.users_vehicles_select_model') }}
                        </option>
                    </select>
                </fieldset>
            </div>

            <form action="{{ route('users.store-vehicles', $user) }}" method="POST" id="vehicle-form">
                @csrf

                <input type="hidden" name="vehicle_id" id="vehicle_id" value="{{ old('vehicle_id') }}">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_plate_number') }}</legend>
                        <input type="text" name="plate_number" id="plate_number" class="input w-full"
                            placeholder="{{ __('personnel.users_vehicles_plate_number_placeholder') }}"
                            value="{{ old('plate_number') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_type') }}</legend>
                        <select class="select w-full" name="vehicle_type" id="vehicle_type">
                            <option value="" disabled {{ old('vehicle_type') ? '' : 'selected' }}>
                                {{ __('personnel.users_vehicles_select_type') }}
                            </option>
                            @foreach ($vehicleTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('vehicle_type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_ownership_type') }}</legend>
                        <select class="select w-full" name="ownership_type" id="ownership_type">
                            <option value="" disabled {{ old('ownership_type') ? '' : 'selected' }}>
                                {{ __('personnel.users_vehicles_select_ownership_type') }}
                            </option>
                            @foreach ($ownershipTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('ownership_type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_purchase_type') }}</legend>
                        <select class="select w-full" name="purchase_type" id="purchase_type">
                            <option value="" disabled {{ old('purchase_type') ? '' : 'selected' }}>
                                {{ __('personnel.users_vehicles_select_purchase_type') }}
                            </option>
                            @foreach ($purchaseTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('purchase_type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_contract_start_date') }}
                        </legend>
                        <input type="date" name="contract_start_date" id="contract_start_date" class="input w-full"
                            value="{{ old('contract_start_date') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_contract_end_date') }}</legend>
                        <input type="date" name="contract_end_date" id="contract_end_date" class="input w-full"
                            value="{{ old('contract_end_date') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_mileage') }}</legend>
                        <input type="number" name="mileage" id="mileage" class="input w-full"
                            placeholder="{{ __('personnel.users_vehicles_mileage_placeholder') }}"
                            value="{{ old('mileage') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vehicles_mileage_update_date') }}
                        </legend>
                        <input type="date" name="mileage_update_date" id="mileage_update_date" class="input w-full"
                            value="{{ old('mileage_update_date') }}" />
                    </fieldset>

                    <button type="submit" class="lg:hidden btn btn-primary w-full"
                        id="submit-button">{{ __('personnel.users_vehicles_add') }}</button>
                </div>
            </form>

        </div>
    </div>

    @push('scripts')
        @vite('resources/js/vehicles.js')
    @endpush

</x-layouts.app>
