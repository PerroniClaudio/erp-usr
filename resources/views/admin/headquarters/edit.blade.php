<x-layouts.app>
    <x-layouts.header :title="__('headquarters.edit_headquarter')">
        <x-slot:actions>
            <a href="{{ route('companies.edit', $headquarter->company_id) }}" class="btn btn-secondary">
                <x-lucide-arrow-left class="w-4 h-4 mr-2" />
                {{ __('headquarters.back_to_company', ['company' => $headquarter->company->name ?? '']) }}
            </a>
            <a class="btn btn-primary" onclick="document.getElementById('headquarter-submit').click()">
                {{ __('headquarters.edit_headquarter') }}
            </a>
        </x-slot>
    </x-layouts.header>

    @unless ($mapboxAccessToken)
        <div class="alert alert-warning mb-4">
            {{ __('headquarters.missing_api_key') }}
        </div>
    @endunless

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card bg-base-300">
        <form class="card-body flex flex-col gap-4" id="headquarter-form" data-headquarter-form method="POST"
            action="{{ route('headquarters.update', $headquarter) }}"
            data-search-url="{{ route('headquarters.search-address') }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('headquarters.name') }}</legend>
                    <input type="text" name="name" class="input w-full"
                        value="{{ old('name', $headquarter->name) }}" placeholder="{{ __('headquarters.name') }}" />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('headquarters.company') }}</legend>
                    <input type="hidden" name="company_id" value="{{ old('company_id', $headquarter->company_id) }}">
                    <select class="select select-bordered w-full" disabled>
                        <option value="" disabled>{{ __('headquarters.select_company') }}</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ old('company_id', $headquarter->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>
            </div>

            <div class="card bg-base-200">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl">{{ __('headquarters.address_validation') }}</h2>
                    </div>

                    <div class="join w-full mt-2">
                        <div class="flex-1">
                            <label class="input validator join-item w-full">
                                <input type="text" id="headquarter-address-search-input"
                                    placeholder="{{ __('headquarters.search_placeholder') }}" />
                            </label>
                        </div>
                        <button class="btn btn-primary join-item" type="button"
                            id="validate-headquarter-address-button">{{ __('headquarters.validate_address') }}</button>
                    </div>
                    <p class="text-error label headquarter-address-error"></p>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-2">
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('headquarters.address') }}</legend>
                            <input type="text" name="address" class="input w-full headquarter-address-field"
                                value="{{ old('address', $headquarter->address) }}"
                                placeholder="{{ __('headquarters.address') }}" disabled />
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('headquarters.city') }}</legend>
                            <input type="text" name="city" class="input w-full headquarter-address-field"
                                value="{{ old('city', $headquarter->city) }}"
                                placeholder="{{ __('headquarters.city') }}" disabled />
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('headquarters.province') }}</legend>
                            <input type="text" name="province" class="input w-full headquarter-address-field"
                                value="{{ old('province', $headquarter->province) }}"
                                placeholder="{{ __('headquarters.province') }}" disabled />
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('headquarters.zip_code') }}</legend>
                            <input type="text" name="zip_code" class="input w-full headquarter-address-field"
                                value="{{ old('zip_code', $headquarter->zip_code) }}"
                                placeholder="{{ __('headquarters.zip_code') }}" disabled />
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('headquarters.latitude') }}</legend>
                            <input type="text" name="latitude" class="input w-full headquarter-address-field"
                                value="{{ old('latitude', $headquarter->latitude) }}"
                                placeholder="{{ __('headquarters.latitude') }}" disabled />
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('headquarters.longitude') }}</legend>
                            <input type="text" name="longitude" class="input w-full headquarter-address-field"
                                value="{{ old('longitude', $headquarter->longitude) }}"
                                placeholder="{{ __('headquarters.longitude') }}" disabled />
                        </fieldset>
                    </div>

                    <div class="mt-4">
                        <div id="headquarter-map" class="w-full h-64 rounded-lg bg-base-200"
                            data-mapbox-token="{{ $mapboxAccessToken }}"
                            data-lat="{{ old('latitude', $headquarter->latitude) }}"
                            data-lng="{{ old('longitude', $headquarter->longitude) }}"
                            data-address="{{ $headquarter->address }}, {{ $headquarter->city }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" id="headquarter-submit" class="btn btn-primary">
                    {{ __('headquarters.edit_headquarter') }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        @vite('resources/js/headquarters.js')
    @endpush
</x-layouts.app>
