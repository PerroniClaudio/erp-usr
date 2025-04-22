<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('business_trips.business_trip_create') }}</h1>
        <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
            {{ __('business_trips.save') }}
        </a>
    </div>

    <hr>

    <div class="card bg-base-300 ">
        <form class="card-body" method="POST" action="{{ route('business-trips.store') }}">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Azienda</legend>
                <select class="select" name="company_id" value="{{ old('company_id') }}">
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Data inizio</legend>
                <input type="date" name="date_from" class="input" value="{{ old('date_from') }}" />

            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Data fine</legend>
                <input type="date" name="date_to" class="input" value="{{ old('date_to') }}" />

            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Stato</legend>
                <select class="select" name="status" value="{{ old('status') }}">
                    <option value="" disabled selected>Seleziona lo stato</option>
                    <option value="0">Aperto</option>
                    <option value="1">Chiuso</option>
                </select>

            </fieldset>

            <button id="submit-button" type="submit" class="hidden"> {{ __('business_trips.save') }}</button>


        </form>
    </div>
</x-layouts.app>
