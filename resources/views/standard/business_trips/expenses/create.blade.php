<x-layouts.app>

    <x-layouts.header :title="__('business_trips.new_expense')">
        <x-slot:actions>
            <div class="hidden submit-button-container">
                <div class="hidden lg:block">
                    <a class="btn btn-primary " onclick="document.getElementById('submit-button').click()">
                        {{ __('business_trips.save') }}
                    </a>
                </div>
            </div>
        </x-slot:actions>
    </x-layouts.header>

    <div role="alert" class="alert alert-info lg:w-1/3">
        <x-lucide-info class="w-8 h-8" />
        <p>Convalida l'indirizzo prima di procedere</p>
    </div>

    <form class="grid lg:grid-cols-2 gap-4" method="POST"
        action="{{ route('business-trips.expenses.store', [
            'businessTrip' => $businessTrip->id,
        ]) }}">
        @csrf

        <input type="hidden" name="business_trip_id" value="{{ $businessTrip->id }}" />

        <div class="card bg-base-300">
            <div class="card-body">
                <h3 class="card-title">Informazioni</h3>
                <hr>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Azienda</legend>
                    <select class="select" name="company_id" value="{{ old('company_id') }}">
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Metodo di pagamento</legend>
                    <select class="select" name="payment_type" value="{{ old('payment_type') }}">
                        <option value="" disabled selected>Seleziona il metodo di pagamento</option>
                        <option value="0" {{ old('payment_type') == 0 ? 'selected' : '' }}>Carta di credito
                            aziendale
                        </option>
                        <option value="1" {{ old('payment_type') == 1 ? 'selected' : '' }}>Carta di credito
                            personale
                        </option>
                        <option value="2" {{ old('payment_type') == 2 ? 'selected' : '' }}>Bancomat aziendale
                        </option>
                        <option value="3" {{ old('payment_type') == 3 ? 'selected' : '' }}>Bancomat personale
                        </option>
                        <option value="4" {{ old('payment_type') == 4 ? 'selected' : '' }}>Anticipo Contante
                        </option>
                        <option value="5" {{ old('payment_type') == 5 ? 'selected' : '' }}>Contante Personale
                        </option>
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Categoria</legend>
                    <select class="select" name="expense_type" value="{{ old('expense_type') }}">
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
            </div>
        </div>

        <div class="card bg-base-300">
            <div class="card-body">
                <h3 class="card-title">Luogo</h3>
                <hr>
                <div>


                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Indirizzo</legend>
                        <input type="text" id="address" name="address" class="input" value="{{ old('address') }}"
                            placeholder="Inserisci l'indirizzo" />
                        <p id="address-error" class="text-error label"></p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Città</legend>
                        <input type="text" id="city" name="city" class="input" value="{{ old('city') }}"
                            placeholder="Inserisci la città" />
                        <p id="city-error" class="text-error label"></p>

                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Provincia</legend>
                        <input type="text" id="province" name="province" class="input"
                            value="{{ old('province') }}" placeholder="Inserisci la provincia" />

                        <p id="province-error" class="text-error label"></p>

                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">CAP</legend>
                        <input type="text" id="zip_code" name="zip_code" class="input"
                            value="{{ old('zip_code') }}" placeholder="Inserisci il CAP" />
                        <p id="zip_code-error" class="text-error label"></p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}" />
                        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}" />
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
            <div class="lg:hidden block">
                <a class="btn btn-primary " onclick="document.getElementById('submit-button').click()">
                    {{ __('business_trips.save') }}
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/businessTrips.js')
    @endpush
</x-layouts.app>
