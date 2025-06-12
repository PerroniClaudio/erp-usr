<x-layouts.app>

    <input type="hidden" id="is_edit" name="is_edit" value="1" />

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('business_trips.edit_expense') }}</h1>


        <div class="hidden lg:inline-flex">
            <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
                {{ __('business_trips.save') }}
            </a>
        </div>
    </div>

    <hr>

    <form class="grid lg:grid-cols-2 gap-4" method="POST"
        action="{{ route('business-trips.expenses.update', [
            'businessTrip' => $businessTrip->id,
            'expense' => $expense->id,
        ]) }}">
        @csrf
        @method('PATCH')

        <input type="hidden" name="business_trip_id" value="{{ $businessTrip->id }}" />

        <div class="card bg-base-300">
            <div class="card-body">
                <h3 class="card-title">Informazioni</h3>
                <hr>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Azienda</legend>
                    <select class="select" name="company_id" value="{{ $expense->company_id }}">
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ $expense->company_id == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Metodo di pagamento</legend>
                    <select class="select" name="payment_type" value="{{ $expense->payment_type }}">
                        <option value="" disabled selected>Seleziona il metodo di pagamento</option>
                        <option value="0" {{ $expense->payment_type == 0 ? 'selected' : '' }}>Carta di credito
                            aziendale
                        </option>
                        <option value="1" {{ $expense->payment_type == 1 ? 'selected' : '' }}>Carta di credito
                            personale
                        </option>
                        <option value="2" {{ $expense->payment_type == 2 ? 'selected' : '' }}>Bancomat aziendale
                        </option>
                        <option value="3" {{ $expense->payment_type == 3 ? 'selected' : '' }}>Bancomat personale
                        </option>
                        <option value="4" {{ $expense->payment_type == 4 ? 'selected' : '' }}>Anticipo Contante
                        </option>
                        <option value="5" {{ $expense->payment_type == 5 ? 'selected' : '' }}>Contante Personale
                        </option>
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Categoria</legend>
                    <select class="select" name="expense_type" value="{{ $expense->expense_type }}">
                        <option value="" disabled selected>Seleziona la categoria di spesa</option>
                        <option value="0" {{ $expense->expense_type == 0 ? 'selected' : '' }}>Pasto
                        </option>
                        <option value="1" {{ $expense->expense_type == 1 ? 'selected' : '' }}>Pedaggio
                        </option>
                        <option value="2" {{ $expense->expense_type == 2 ? 'selected' : '' }}>Parcheggio</option>
                        <option value="3" {{ $expense->expense_type == 3 ? 'selected' : '' }}>Trasporto</option>
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Data</legend>
                    <input type="datetime-local" name="date" class="input" value="{{ $expense->date }}" />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Importo</legend>
                    <input type="number" name="amount" class="input" value="{{ $expense->amount }}"
                        placeholder="0,00" step="0.01" />
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
                            value="{{ $expense->address }}" placeholder="Inserisci l'indirizzo" />
                        <p id="address-error" class="text-error label"></p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Città</legend>
                        <input type="text" id="city" name="city" class="input" value="{{ $expense->city }}"
                            placeholder="Inserisci la città" />
                        <p id="city-error" class="text-error label"></p>

                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Provincia</legend>
                        <input type="text" id="province" name="province" class="input"
                            value="{{ $expense->province }}" placeholder="Inserisci la provincia" />

                        <p id="province-error" class="text-error label"></p>

                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">CAP</legend>
                        <input type="text" id="zip_code" name="zip_code" class="input"
                            value="{{ $expense->zip_code }}" placeholder="Inserisci il CAP" />
                        <p id="zip_code-error" class="text-error label"></p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <input type="hidden" id="latitude" name="latitude" value="{{ $expense->latitude }}" />
                        <input type="hidden" id="longitude" name="longitude" value="{{ $expense->longitude }}" />
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

    <div class="grid lg:grid-cols-2 gap-4">
        <div class="card bg-base-300">
            <div class="card-body">
                <h3 class="card-title">
                    {{ __('business_trips.expense_justification') }}
                </h3>
                <hr>


                <div class="btn btn-primary" id="upload-start">
                    {{ __('business_trips.expense_justification_select_file') }}
                </div>
                <div class="btn btn-success hidden" id="upload-success">
                    {{ __('business_trips.expense_justification_upload') }}
                </div>




                <div class="hidden" id="upload-info">
                    <div class="alert alert-info flex items-center justify-between">
                        <span id="filename-display"></span>
                        <div class="btn btn-ghost">
                            <x-lucide-x class="w-4 h-4" id="cancel-upload-button" />
                        </div>
                    </div>
                </div>

                @if ($expense->justification_file_name != null)
                    <a href="{{ route('business-trips.expenses.download-justification', [
                        'businessTripExpense' => $expense->id,
                    ]) }}"
                        target="_blank" class="btn btn-primary">
                        {{ __('business_trips.expense_justification_download') }}
                    </a>
                @endif


            </div>
        </div>
    </div>



    <div class="lg:hidden block">
        <div class="flex flex-row-reverse">
            <a class="btn btn-primary " onclick="document.getElementById('submit-button').click()">
                {{ __('business_trips.save') }}
            </a>
        </div>
    </div>

    <form
        action="{{ route('business-trips.expenses.upload-justification', [
            'businessTripExpense' => $expense->id,
        ]) }}"
        method="POST" enctype="multipart/form-data" class="hidden">
        @csrf
        @method('PUT')
        <input type="file" id="file-input" name="justification_file" class="hidden"
            accept=".jpg, .jpeg, .png, .pdf" />

        <button id="submit-file-button"></button>
    </form>

    @push('scripts')
        @vite('resources/js/businessTrips.js')
    @endpush
</x-layouts.app>
