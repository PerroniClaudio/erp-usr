<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('business_trips.edit_transfer') }}</h1>


        <div class="hidden submit-button-container">
            <a class="btn btn-primary hidden lg:inline-flex" onclick="document.getElementById('submit-button').click()">
                {{ __('business_trips.save') }}
            </a>
        </div>
    </div>

    <hr>

    <div role="alert" class="alert alert-info lg:w-1/3">
        <x-lucide-info class="w-8 h-8" />
        <p>Convalida l'indirizzo prima di procedere</p>
    </div>

    <form class="grid lg:grid-cols-2 gap-4" method="POST"
        action="{{ route('business-trips.transfers.update', [
            'businessTrip' => $businessTrip->id,
            'transfer' => $transfer->id,
        ]) }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="business_trip_id" value="{{ $businessTrip->id }}" />

        <div class="card bg-base-300">
            <div class="card-body">
                <h3 class="card-title">Informazioni</h3>
                <hr>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Azienda</legend>
                    <select class="select" name="company_id" value="{{ $transfer->company_id }}">
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ $transfer->company_id == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Data</legend>
                    <input type="datetime-local" name="date" class="input" value="{{ $transfer->date }}" />
                </fieldset>

            </div>
        </div>

        <div class="card bg-base-300">
            <div class="card-body">
                <h3 class="card-title">Luogo</h3>
                <hr>
                <div>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Indirizzo</legend>
                        <input type="text" id="address" name="address" class="input"
                            value="{{ $transfer->address }}" placeholder="Inserisci l'indirizzo" />
                        <p id="address-error" class="text-error label"></p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Città</legend>
                        <input type="text" id="city" name="city" class="input"
                            value="{{ $transfer->city }}" placeholder="Inserisci la città" />
                        <p id="city-error" class="text-error label"></p>

                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Provincia</legend>
                        <input type="text" id="province" name="province" class="input"
                            value="{{ $transfer->province }}" placeholder="Inserisci la provincia" />

                        <p id="province-error" class="text-error label"></p>

                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">CAP</legend>
                        <input type="text" id="zip_code" name="zip_code" class="input"
                            value="{{ $transfer->zip_code }}" placeholder="Inserisci il CAP" />
                        <p id="zip_code-error" class="text-error label"></p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <input type="hidden" id="latitude" name="latitude" value="{{ $transfer->latitude }}" />
                        <input type="hidden" id="longitude" name="longitude" value="{{ $transfer->longitude }}" />
                    </fieldset>

                    <p class="text-error text-sm my-2" id="error-message"></p>

                    <div class="flex items-center justify-between">
                        <div class="btn btn-secondary" id="validate-address">Convalida</div>

                        <div>
                            <x-lucide-check class="w-8 h-8 hidden text-success" id="address-valid-icon" />
                            <x-lucide-x class="w-8 h-8 hidden text-error" id="address-invalid-icon" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button id="submit-button"></button>

    </form>

    <div class="hidden submit-button-container">
        <div class="flex flex-row-reverse">
            <a class="btn btn-primary lg:hidden block" onclick="document.getElementById('submit-button').click()">
                {{ __('business_trips.save') }}
            </a>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/businessTrips.js')
    @endpush
</x-layouts.app>
