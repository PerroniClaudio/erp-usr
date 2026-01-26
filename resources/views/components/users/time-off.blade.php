@vite('resources/js/time_off_amount.js')

<div class="card bg-base-300 max-w-full" id="time-off-card">
    <div class="card-body">
        <div class="flex justify-between items-center">
            <h3 class="card-title">{{ __('personnel.users_time_off_and_rol_management') }}</h3>
            <button class="btn btn-primary" id="add-time-off-btn" type="button" data-user-id="{{ $user->id }}"
                onclick="add_time_off_modal.showModal()">
                <x-lucide-plus class="h-4 w-4" />
            </button>
        </div>
        <hr>
        <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ __('personnel.users_time_off_and_rol_select_month') }}</legend>
            <div class="grid grid-cols-3 gap-2">
                <select name="month_filter" id="month-filter" class="select w-full form-input-activable">
                    @for ($month = 1; $month <= 12; $month++)
                        <option value="{{ $month }}" @if ($month == now()->month) selected @endif>
                            {{ \Carbon\Carbon::create()->month($month)->locale('it')->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year_filter" id="year-filter" class="select w-full form-input-activable">
                    @for ($year = now()->year; $year >= now()->year - 4; $year--)
                        <option value="{{ $year }}" @if ($year == now()->year) selected @endif>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
                <button type="button" class="btn btn-primary" id="time-off-search">
                    {{ __('personnel.users_time_off_search') }}
                </button>

            </div>

        </fieldset>

        <div id="time-off-content">
            <div class="grid xl:grid-cols-2 gap-4 mt-4" id="time-off-overview"
                data-month-url="{{ route('time-off-amounts.monthly') }}"
                data-usage-url="{{ route('time-off-amounts.usage') }}">
                <div class="flex flex-col gap-4">
                    <div class="grid grid-cols-2 gap-4">
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('personnel.users_time_off_total_label') }}</legend>
                            <input type="number" id="time-off-total-input" class="input w-full form-input-activable"
                                placeholder="{{ __('personnel.users_time_off_total_label') }}" />
                        </fieldset>
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('personnel.users_time_off_used_label') }}</legend>
                            <input type="number" id="time-off-used-input" class="input w-full form-input-activable"
                                placeholder="{{ __('personnel.users_time_off_used_label') }}" />
                        </fieldset>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('personnel.users_rol_total_label') }}</legend>
                            <input type="number" id="rol-total-input" class="input w-full form-input-activable"
                                placeholder="{{ __('personnel.users_rol_total_label') }}" />
                        </fieldset>
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">{{ __('personnel.users_rol_used_label') }}</legend>
                            <input type="number" id="rol-used-input" class="input w-full form-input-activable"
                                placeholder="{{ __('personnel.users_rol_used_label') }}" />
                        </fieldset>
                    </div>
                </div>
                <div class="card bg-base-200">
                    <div class="card-body flex justify-center">
                        <div class="grid grid-cols-2">
                            <div class="flex flex-col items-center gap-8">
                                <div class="badge badge-primary badge-xl">
                                    {{ __('personnel.users_time_off_remaining_label') }}</div>
                                <p class="text-3xl font-bold" id="time-off-remaining-label"
                                    data-template="{{ __('personnel.users_time_off_hours', ['hours' => ':hours']) }}">
                                    {{ __('personnel.users_time_off_hours', ['hours' => 120]) }}
                                </p>
                            </div>
                            <div class="flex flex-col items-center gap-8">
                                <div class="badge badge-secondary badge-xl">
                                    {{ __('personnel.users_rol_remaining_label') }}</div>
                                <p class="text-3xl font-bold" id="rol-remaining-label"
                                    data-template="{{ __('personnel.users_rol_hours', ['hours' => ':hours']) }}">
                                    {{ __('personnel.users_rol_hours', ['hours' => 120]) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h4 class="text-lg font-semibold mb-2">{{ __('personnel.users_time_off_trend_title') }}</h4>
                <div class="card bg-base-200">
                    <div class="card-body">
                        <canvas id="time-off-usage-chart" height="120"></canvas>
                    </div>
                </div>
                <div id="time-off-usage-warning" class="alert alert-warning mt-3 hidden">
                    <span>
                        Dati del monte ore non disponibili per l'anno selezionato. Il grafico usa il residuo di
                        fine anno e potrebbe non essere accurato.
                    </span>
                </div>
            </div>
        </div>

        <div class="hidden items-center justify-center" id="time-off-loading">
            <span class="loading loading-spinner loading-xl text-primary"></span>
        </div>

    </div>
</div>

<dialog class="modal" id="add_time_off_modal" data-user-id="{{ $user->id }}"
    data-calculate-url="{{ route('time-off-amounts.calculate') }}"
    data-store-url="{{ route('time-off-amounts.store') }}">
    <div class="modal-box min-w-3/4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold"> {{ __('personnel.users_time_off_add_title') }}</h3>
            <form method="dialog">
                <button class="btn btn-ghost">
                    <x-lucide-x class="w-4 h-4" />
                </button>
            </form>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-2">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_time_off_add_insert_date') }}</legend>
                    <input type="date" name="insert_date" class="input w-full form-input-activable"
                        value="{{ now()->format('Y-m-d') }}"
                        placeholder="{{ __('personnel.users_time_off_add_insert_date') }}" />
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Tipo inserimento</legend>
                    <select id="time-off-entry-type" class="select w-full form-input-activable">
                        <option value="total">Monte ore (01/01)</option>
                        <option value="residual">Residuo (31/12)</option>
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Anno</legend>
                    <select id="time-off-entry-year" class="select w-full form-input-activable">
                        @for ($year = now()->year + 1; $year >= now()->year - 4; $year--)
                            <option value="{{ $year }}" @if ($year == now()->year) selected @endif>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </fieldset>
                <input type="hidden" name="reference_date" id="reference-date-input"
                    value="{{ now()->format('Y-m-d') }}" />
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_time_off_add_time_off_amount') }}</legend>
                    <input type="number" name="time_off_amount" id="time-off-amount-input"
                        class="input w-full form-input-activable"
                        placeholder="{{ __('personnel.users_time_off_add_time_off_amount') }}" />
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_time_off_add_rol_amount') }}</legend>
                    <input type="number" name="rol_amount" id="rol-amount-input"
                        class="input w-full form-input-activable"
                        placeholder="{{ __('personnel.users_time_off_add_rol_amount') }}" />
                </fieldset>
            </div>
            <div class="card bg-base-200">
                <div class="card-body flex justify-center">
                    <div class="grid xl:grid-cols-2 gap-4">
                        <div class="flex flex-col items-center gap-2 xl:gap-8">
                            <div class="badge badge-primary badge-xl">
                                {{ __('personnel.users_time_off_remaining_label') }}</div>
                            <p class="text-3xl font-bold" id="time-off-remaining-label-modal"
                                data-template="{{ __('personnel.users_time_off_hours', ['hours' => ':hours']) }}">
                                {{ __('personnel.users_time_off_hours', ['hours' => 120]) }}
                            </p>
                        </div>
                        <div class="flex flex-col items-center gap-2 xl:gap-8">
                            <div class="badge badge-secondary badge-xl">
                                {{ __('personnel.users_rol_remaining_label') }}</div>
                            <p class="text-3xl font-bold" id="rol-remaining-label-modal"
                                data-template="{{ __('personnel.users_rol_hours', ['hours' => ':hours']) }}">
                                {{ __('personnel.users_rol_hours', ['hours' => 120]) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-action">
            <button class="btn btn-primary"
                id="save-time-off-amount">{{ __('personnel.users_time_off_save') }}</button>
            <form method="dialog">
                <button class="btn btn-ghost">{{ __('personnel.users_time_off_close') }}</button>
            </form>
        </div>
    </div>
</dialog>
