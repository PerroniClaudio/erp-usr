<x-layouts.app>

    <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('personnel.users_edit_user') }}</h1>
    </div>

    <hr>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card bg-base-300 ">
            <form class="card-body" method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                <div class="flex items-center justify-between">
                    <h2 class="text-lg">{{ __('personnel.users_personal_data') }}</h2>
                    <div class="btn btn-primary"
                        onclick="document.getElementById('submit-button-personal-data').click()">
                        <x-lucide-save class="h-4 w-4" />
                    </div>
                </div>

                <hr>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_title') }}</legend>
                        <input type="text" name="title" class="input w-full"
                            value="{{ old('title', $user->title) }}" placeholder="{{ __('personnel.users_title') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_name') }}</legend>
                        <input type="text" name="name" class="input w-full" value="{{ old('name', $user->name) }}"
                            placeholder="{{ __('personnel.users_name') }}" disabled />
                    </fieldset>

                    <fieldset class="fieldset col-span-2">
                        <legend class="fieldset-legend">{{ __('personnel.users_email') }}</legend>
                        <input type="email" name="email" class="input w-full"
                            value="{{ old('email', $user->email) }}" placeholder="{{ __('personnel.users_email') }}"
                            disabled />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_cfp') }}</legend>
                        <input type="text" name="cfp" class="input w-full" value="{{ old('cfp', $user->cfp) }}"
                            placeholder="{{ __('personnel.users_cfp') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_birth_date') }}</legend>
                        <input type="date" name="birth_date" class="input w-full"
                            value="{{ old('birth_date', $user->birth_date) }}"
                            placeholder="{{ __('personnel.users_birth_date') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_company_name') }}</legend>
                        <input type="text" name="company_name" class="input w-full"
                            value="{{ old('company_name', $user->company_name) }}"
                            placeholder="{{ __('personnel.users_company_name') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_vat_number') }}</legend>
                        <input type="text" name="vat_number" class="input w-full"
                            value="{{ old('vat_number', $user->vat_number) }}"
                            placeholder="{{ __('personnel.users_vat_number') }}" />
                    </fieldset>


                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_mobile_number') }}</legend>
                        <input type="text" name="mobile_number" class="input w-full"
                            value="{{ old('mobile_number', $user->mobile_number) }}"
                            placeholder="{{ __('personnel.users_mobile_number') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_phone_number') }}</legend>
                        <input type="text" name="phone_number" class="input w-full"
                            value="{{ old('phone_number', $user->phone_number) }}"
                            placeholder="{{ __('personnel.users_phone_number') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_weekly_hours') }}</legend>
                        <input type="number" name="weekly_hours" class="input w-full"
                            value="{{ old('weekly_hours', $user->weekly_hours) }}"
                            placeholder="{{ __('personnel.users_weekly_hours') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_category') }}</legend>
                        <select name="category" class="select w-full">
                            <option value="Dipendente"
                                {{ old('category', $user->category) == 'Dipendente' ? 'selected' : '' }}>
                                {{ __('personnel.users_category_employee') }}
                            </option>
                            <option value="Consulente"
                                {{ old('category', $user->category) == 'Consulente' ? 'selected' : '' }}>
                                {{ __('personnel.users_category_consultant') }}
                            </option>
                            <option value="Stagista"
                                {{ old('category', $user->category) == 'Stagista' ? 'selected' : '' }}>
                                {{ __('personnel.users_category_intern') }}
                            </option>
                        </select>
                    </fieldset>


                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_badge_code') }}</legend>
                        <input type="text" name="badge_code" class="input w-full"
                            value="{{ old('badge_code', $user->badge_code) }}"
                            placeholder="{{ __('personnel.users_badge_code') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_employee_code') }}</legend>
                        <input type="text" name="employee_code" class="input w-full"
                            value="{{ old('employee_code', $user->employee_code) }}"
                            placeholder="{{ __('personnel.users_employee_code') }}" />
                    </fieldset>

                    <button id="submit-button-personal-data" type="submit" class="hidden"></button>
                </div>
            </form>
        </div>

        <div class="card bg-base-300">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg">{{ __('personnel.users_time_off_and_rol') }}</h2>
                    <div class="btn btn-primary"
                        onclick="document.getElementById('submit-button-personal-data').click()">
                        <x-lucide-save class="h-4 w-4" />
                    </div>
                </div>

                <hr>

                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="card-title">
                            {{ __('personnel.users_time_off') }}
                        </div>

                        <hr>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_time_off_accrued') }}</legend>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <input type="text" class="input" placeholder="0" />
                                        <p class="label">{{ __('personnel.users_days') }}</p>
                                    </div>
                                    <div>
                                        <input type="text" class="input" placeholder="0" />
                                        <p class="label">{{ __('personnel.users_hours') }}</p>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_time_off_amount') }}</legend>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <input type="text" class="input" placeholder="0" />
                                        <p class="label">{{ __('personnel.users_days') }}</p>
                                    </div>
                                    <div>
                                        <input type="text" class="input" placeholder="0" />
                                        <p class="label">{{ __('personnel.users_hours') }}</p>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_time_off_used') }}</legend>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <input type="text" class="input" placeholder="0" />
                                        <p class="label">{{ __('personnel.users_days') }}</p>
                                    </div>
                                    <div>
                                        <input type="text" class="input" placeholder="0" />
                                        <p class="label">{{ __('personnel.users_hours') }}</p>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_time_off_remaining') }}
                                </legend>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <input type="text" class="input" placeholder="0" />
                                        <p class="label">{{ __('personnel.users_days') }}</p>
                                    </div>
                                    <div>
                                        <input type="text" class="input" placeholder="0" />
                                        <p class="label">{{ __('personnel.users_hours') }}</p>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="card-title">
                            {{ __('personnel.users_rol') }}
                        </div>

                        <hr>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_rol_accrued') }}</legend>
                                <input type="number" class="input w-full" value="0" />
                                <p class="label">{{ __('personnel.users_hours') }}</p>
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_rol_amount') }}</legend>
                                <input type="number" class="input w-full" value="0" />
                                <p class="label">{{ __('personnel.users_hours') }}</p>
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_rol_used') }}</legend>
                                <input type="number" class="input w-full" value="0" />
                                <p class="label">{{ __('personnel.users_hours') }}</p>
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_rol_remaining') }}</legend>
                                <input type="number" class="input w-full" value="0" />
                                <p class="label">{{ __('personnel.users_hours') }}</p>
                            </fieldset>
                        </div>

                    </div>
                </div>


            </div>
        </div>

        <div class="card bg-base-300 ">
            <div class="card-body">
                <div class="card-title flex items-center justify-between gap-2">
                    <span>{{ __('personnel.users_residence') }}</span>

                    <div class="btn btn-primary" id="submit-button-residence">
                        <x-lucide-save class="h-4 w-4" />
                    </div>
                </div>

                <hr>

                <div>
                    <div class="join w-full">
                        <div class="flex-1">
                            <label class="input validator join-item w-full">
                                <input type="text" id="address-search-input" placeholder="Via Garibaldi 22" />
                            </label>
                        </div>
                        <button class="btn btn-primary join-item" id="validate-address-button">Convalida</button>
                    </div>
                </div>

                <p class="text-error label"></p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_address') }}</legend>
                        <input type="text" name="address" class="input w-full residence-form"
                            value="{{ old('address', $user->address) }}"
                            placeholder="{{ __('personnel.users_address') }}" disabled />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_street_number') }}</legend>
                        <input type="text" name="street_number" class="input w-full residence-form"
                            value="{{ old('street_number', $user->street_number) }}"
                            placeholder="{{ __('personnel.users_street_number') }}" disabled />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_city') }}</legend>
                        <input type="text" name="city" class="input w-full residence-form"
                            value="{{ old('city', $user->city) }}" placeholder="{{ __('personnel.users_city') }}"
                            disabled />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_postal_code') }}</legend>
                        <input type="text" name="postal_code" class="input w-full residence-form"
                            value="{{ old('postal_code', $user->postal_code) }}"
                            placeholder="{{ __('personnel.users_postal_code') }}" disabled />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_province') }}</legend>
                        <input type="text" name="province" class="input w-full residence-form"
                            value="{{ old('province', $user->province) }}"
                            placeholder="{{ __('personnel.users_province') }}" disabled />
                    </fieldset>

                    <div></div>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_latitude') }}</legend>
                        <input type="text" name="latitude" class="input w-full residence-form"
                            value="{{ old('latitude', $user->latitude) }}"
                            placeholder="{{ __('personnel.users_latitude') }}" disabled />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_longitude') }}</legend>
                        <input type="text" name="longitude" class="input w-full residence-form"
                            value="{{ old('longitude', $user->longitude) }}"
                            placeholder="{{ __('personnel.users_longitude') }}" disabled />
                    </fieldset>
                </div>

            </div>
        </div>

        <div class="card bg-base-300 ">
            <div class="card-body">

                <div class="card-title flex items-center justify-between gap-2">
                    <span>{{ __('personnel.users_location') }}</span>

                    <div class="btn btn-primary" id="submit-button-location">
                        <x-lucide-save class="h-4 w-4" />
                    </div>
                </div>

                <hr>

                <div>
                    <div class="join w-full">
                        <div class="flex-1">
                            <label class="input validator join-item w-full">
                                <input type="text" id="location-address-search-input"
                                    placeholder="Via Garibaldi 22" />
                            </label>
                        </div>
                        <button class="btn btn-primary join-item"
                            id="validate-location-address-button">Convalida</button>
                    </div>
                </div>

                <p class="text-error label"></p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_address') }}</legend>
                        <input type="text" name="address" class="input w-full location-form"
                            value="{{ old('location_address', $user->location_address) }}"
                            placeholder="{{ __('personnel.users_address') }}" disabled />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_street_number') }}</legend>
                        <input type="text" name="street_number" class="input w-full location-form"
                            value="{{ old('location_street_number', $user->location_street_number) }}"
                            placeholder="{{ __('personnel.users_street_number') }}" disabled />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_city') }}</legend>
                        <input type="text" name="city" class="input w-full location-form"
                            value="{{ old('location_city', $user->location_city) }}"
                            placeholder="{{ __('personnel.users_city') }}" disabled />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_postal_code') }}</legend>
                        <input type="text" name="postal_code" class="input w-full location-form"
                            value="{{ old('location_postal_code', $user->location_postal_code) }}"
                            placeholder="{{ __('personnel.users_postal_code') }}" disabled />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_province') }}</legend>
                        <input type="text" name="province" class="input w-full location-form"
                            value="{{ old('location_province', $user->location_province) }}"
                            placeholder="{{ __('personnel.users_province') }}" disabled />
                    </fieldset>

                    <div></div>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_latitude') }}</legend>
                        <input type="text" name="latitude" class="input w-full location-form"
                            value="{{ old('location_latitude', $user->location_latitude) }}"
                            placeholder="{{ __('personnel.users_latitude') }}" disabled />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_longitude') }}</legend>
                        <input type="text" name="longitude" class="input w-full location-form"
                            value="{{ old('location_longitude', $user->location_longitude) }}"
                            placeholder="{{ __('personnel.users_longitude') }}" disabled />
                    </fieldset>
                </div>

            </div>
        </div>

        <x-users.vehicles :user="$user" />

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <x-users.companies :user="$user" />

        </div>
    </div>


    @push('scripts')
        @vite('resources/js/users.js')
    @endpush


</x-layouts.app>
