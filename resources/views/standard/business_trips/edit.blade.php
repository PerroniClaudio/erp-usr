<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('business_trips.business_trip_edit') }}</h1>

    </div>

    <hr>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="col-span-1 lg:col-span-1">
            <div class="card bg-base-300 w-full">
                <form class="card-body" method="POST"
                    action="{{ route('business-trips.update', ['businessTrip' => $businessTrip->id]) }}">
                    @csrf
                    @method('PUT')

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Azienda</legend>
                        <select class="select w-full" name="company_id" value="{{ old('company_id') }}">
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ $businessTrip->company_id == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>

                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Data inizio</legend>
                        <input type="date" name="date_from" class="input w-full"
                            value="{{ \Carbon\Carbon::parse($businessTrip->date_from)->format('Y-m-d') }}" />

                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Data fine</legend>
                        <input type="date" name="date_to" class="input w-full"
                            value="{{ \Carbon\Carbon::parse($businessTrip->date_to)->format('Y-m-d') }}" />

                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Stato</legend>
                        <select class="select w-full" name="status" value="{{ old('status') }}">
                            <option value="" disabled selected>Seleziona lo stato</option>
                            <option value="0" {{ $businessTrip->status == 0 ? 'selected' : '' }}>Aperto</option>
                            <option value="1" {{ $businessTrip->status == 1 ? 'selected' : '' }}>Chiuso</option>
                        </select>

                    </fieldset>

                    <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
                        {{ __('business_trips.save') }}
                    </a>

                    <button id="submit-button" type="submit" class="hidden"> {{ __('business_trips.save') }}</button>
                </form>
            </div>
        </div>

        <div class="col-span-1 lg:col-span-3">
            <div class="flex flex-col gap-4">
                <x-business_trips.expenses :businessTrip="$businessTrip" :expenses="$expenses" />
            </div>
        </div>

    </div>



</x-layouts.app>
