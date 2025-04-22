<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('business_trips.new_expense') }}</h1>
        <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
            {{ __('business_trips.save') }}
        </a>
    </div>

    <hr>

    <div class="card bg-base-300">
        <form class="card-body" method="POST"
            action="{{ route('business-trips.expenses.store', [
                'businessTrip' => $businessTrip->id,
            ]) }}">
            @csrf

            <input type="hidden" name="business_trip_id" value="{{ $businessTrip->id }}" />

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
                <legend class="fieldset-legend">Metodo di pagamento</legend>
                <select class="select" name="company_id" value="{{ old('payment_type') }}">
                    <option value="" disabled selected>Seleziona il metodo di pagamento</option>
                    <option value="0" {{ old('payment_type') == 0 ? 'selected' : '' }}>Carta di credito aziendale
                    </option>
                    <option value="1" {{ old('payment_type') == 1 ? 'selected' : '' }}>Carta di credito personale
                    </option>
                    <option value="2" {{ old('payment_type') == 2 ? 'selected' : '' }}>Bancomat aziendale</option>
                    <option value="3" {{ old('payment_type') == 3 ? 'selected' : '' }}>Bancomat personale</option>
                    <option value="4" {{ old('payment_type') == 4 ? 'selected' : '' }}>Anticipo Contante</option>
                    <option value="5" {{ old('payment_type') == 5 ? 'selected' : '' }}>Contante Personale</option>
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Categoria</legend>
                <select class="select" name="company_id" value="{{ old('expense_type') }}">
                    <option value="" disabled selected>Seleziona la categoria di spesa</option>
                    <option value="0" {{ old('expense_type') == 0 ? 'selected' : '' }}>Pasto
                    </option>
                    <option value="1" {{ old('expense_type') == 1 ? 'selected' : '' }}>Pedaggio
                    </option>
                    <option value="2" {{ old('expense_type') == 2 ? 'selected' : '' }}>Parcheggio</option>
                    <option value="3" {{ old('expense_type') == 3 ? 'selected' : '' }}>Trasporto</option>
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Data</legend>
                <input type="datetime-local" name="date" class="input" value="{{ old('date') }}" />
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Importo</legend>
                <input type="number" name="amount" class="input" value="{{ old('amount') }}" placeholder="0,00"
                    step="0.01" />
            </fieldset>



        </form>

    </div>
</x-layouts.app>
