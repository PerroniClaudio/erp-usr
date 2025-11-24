<x-layouts.app>

    <div id="profile-page" data-search-url="{{ route('standard.profile.search-address') }}">
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-semibold">Profilo utente</h1>
            <p class="text-base-content/70">Aggiorna i tuoi dati anagrafici e gli indirizzi di residenza e recapito.</p>
        </div>

        <hr>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 my-4">
            <div class="card bg-base-300 xl:col-span-2">
                <form class="card-body flex flex-col gap-4" method="POST" action="{{ route('standard.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center justify-between">
                        <h2 class="text-lg">{{ __('personnel.users_personal_data') }}</h2>
                        <button type="submit" class="btn btn-primary">
                            <x-lucide-save class="h-4 w-4" />
                            {{ __('personnel.users_save') }}
                        </button>
                    </div>

                    <hr>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('personnel.users_title') }}</legend>
                            <input type="text" name="title" class="input w-full"
                                value="{{ old('title', $user->title) }}" placeholder="{{ __('personnel.users_title') }}"
                                required />
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('personnel.users_name') }}</legend>
                            <input type="text" name="name" class="input w-full"
                                value="{{ old('name', $user->name) }}" placeholder="{{ __('personnel.users_name') }}"
                                required />
                        </fieldset>

                        <fieldset class="fieldset col-span-2">
                            <legend class="fieldset-legend">{{ __('personnel.users_email') }}</legend>
                            <input type="email" name="email" class="input w-full"
                                value="{{ old('email', $user->email) }}"
                                placeholder="{{ __('personnel.users_email') }}" required />
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('personnel.users_cfp') }}</legend>
                            <input type="text" name="cfp" class="input w-full"
                                value="{{ old('cfp', $user->cfp) }}" placeholder="{{ __('personnel.users_cfp') }}"
                                required />
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('personnel.users_birth_date') }}</legend>
                            <input type="date" name="birth_date" class="input w-full"
                                value="{{ old('birth_date', $user->birth_date) }}"
                                placeholder="{{ __('personnel.users_birth_date') }}" required />
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
                    </div>
                </form>
            </div>

            <div class="card bg-base-300">
                <div class="card-body gap-3">
                    <h2 class="text-lg">Dati non modificabili</h2>
                    <p class="text-sm text-base-content/70">Gestiti dall'azienda. Contatta HR per eventuali variazioni.
                    </p>
                    <hr>
                    <div class="grid grid-cols-1 gap-2">
                        <div class="flex items-center justify-between rounded-lg bg-base-200 px-3 py-2">
                            <span class="text-sm">{{ __('personnel.users_weekly_hours') }}</span>
                            <span class="font-semibold">{{ $user->weekly_hours ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-base-200 px-3 py-2">
                            <span class="text-sm">{{ __('personnel.users_category') }}</span>
                            <span class="font-semibold">{{ $user->category ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-base-200 px-3 py-2">
                            <span class="text-sm">{{ __('personnel.users_employee_code') }}</span>
                            <span class="font-semibold">{{ $user->employee_code ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-base-200 px-3 py-2">
                            <span class="text-sm">{{ __('personnel.users_badge_code') }}</span>
                            <span class="font-semibold">{{ $user->badge_code ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="card bg-base-300">
                <form class="card-body flex flex-col gap-4" method="POST"
                    action="{{ route('standard.profile.residence') }}">
                    @csrf

                    <div class="flex items-center justify-between">
                        <h2 class="text-lg">{{ __('personnel.users_residence') }}</h2>
                        <button type="submit" class="btn btn-primary">
                            <x-lucide-save class="h-4 w-4" />
                            {{ __('personnel.users_save') }}
                        </button>
                    </div>

                    <div>
                        <div class="join w-full">
                            <input type="text" id="profile-residence-search" class="input join-item w-full"
                                placeholder="Via Garibaldi 22" autocomplete="off">
                            <button type="button" class="btn btn-primary join-item"
                                data-search="residence">Convalida</button>
                        </div>
                        <p class="text-error text-sm min-h-5" id="residence-search-error"></p>
                    </div>

                    <hr>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_address') }}</legend>
                                <input type="text" name="address" class="input w-full residence-field"
                                    value="{{ old('address', $user->address) }}"
                                    placeholder="{{ __('personnel.users_address') }}" required />
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_street_number') }}</legend>
                                <input type="text" name="street_number" class="input w-full residence-field"
                                    value="{{ old('street_number', $user->street_number) }}"
                                    placeholder="{{ __('personnel.users_street_number') }}" required />
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_city') }}</legend>
                                <input type="text" name="city" class="input w-full residence-field"
                                    value="{{ old('city', $user->city) }}"
                                    placeholder="{{ __('personnel.users_city') }}" required />
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_postal_code') }}</legend>
                                <input type="text" name="postal_code" class="input w-full residence-field"
                                    value="{{ old('postal_code', $user->postal_code) }}"
                                    placeholder="{{ __('personnel.users_postal_code') }}" required />
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_province') }}</legend>
                                <input type="text" name="province" class="input w-full residence-field"
                                    value="{{ old('province', $user->province) }}"
                                    placeholder="{{ __('personnel.users_province') }}" required />
                            </fieldset>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_latitude') }}</legend>
                                <input type="text" name="latitude" class="input w-full residence-field"
                                    value="{{ old('latitude', $user->latitude) }}"
                                    placeholder="{{ __('personnel.users_latitude') }}" required />
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_longitude') }}</legend>
                                <input type="text" name="longitude" class="input w-full residence-field"
                                    value="{{ old('longitude', $user->longitude) }}"
                                    placeholder="{{ __('personnel.users_longitude') }}" required />
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card bg-base-300">
                <form class="card-body flex flex-col gap-4" method="POST"
                    action="{{ route('standard.profile.location') }}">
                    @csrf

                    <div class="flex items-center justify-between">
                        <h2 class="text-lg">{{ __('personnel.users_location') }}</h2>
                        <button type="submit" class="btn btn-primary">
                            <x-lucide-save class="h-4 w-4" />
                            {{ __('personnel.users_save') }}
                        </button>
                    </div>

                    <div>
                        <div class="join w-full">
                            <input type="text" id="profile-location-search" class="input join-item w-full"
                                placeholder="Via Garibaldi 22" autocomplete="off">
                            <button type="button" class="btn btn-primary join-item"
                                data-search="location">Convalida</button>
                        </div>
                        <p class="text-error text-sm min-h-5" id="location-search-error"></p>
                    </div>

                    <hr>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_address') }}</legend>
                                <input type="text" name="location_address" class="input w-full location-field"
                                    value="{{ old('location_address', $user->location_address) }}"
                                    placeholder="{{ __('personnel.users_address') }}" required />
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_street_number') }}</legend>
                                <input type="text" name="location_street_number"
                                    class="input w-full location-field"
                                    value="{{ old('location_street_number', $user->location_street_number) }}"
                                    placeholder="{{ __('personnel.users_street_number') }}" required />
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_city') }}</legend>
                                <input type="text" name="location_city" class="input w-full location-field"
                                    value="{{ old('location_city', $user->location_city) }}"
                                    placeholder="{{ __('personnel.users_city') }}" required />
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_postal_code') }}</legend>
                                <input type="text" name="location_postal_code" class="input w-full location-field"
                                    value="{{ old('location_postal_code', $user->location_postal_code) }}"
                                    placeholder="{{ __('personnel.users_postal_code') }}" required />
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_province') }}</legend>
                                <input type="text" name="location_province" class="input w-full location-field"
                                    value="{{ old('location_province', $user->location_province) }}"
                                    placeholder="{{ __('personnel.users_province') }}" required />
                            </fieldset>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_latitude') }}</legend>
                                <input type="text" name="location_latitude" class="input w-full location-field"
                                    value="{{ old('location_latitude', $user->location_latitude) }}"
                                    placeholder="{{ __('personnel.users_latitude') }}" required />
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">{{ __('personnel.users_longitude') }}</legend>
                                <input type="text" name="location_longitude" class="input w-full location-field"
                                    value="{{ old('location_longitude', $user->location_longitude) }}"
                                    placeholder="{{ __('personnel.users_longitude') }}" required />
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/profile.js')
    @endpush
</x-layouts.app>
