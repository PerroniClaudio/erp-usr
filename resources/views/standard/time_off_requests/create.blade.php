<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('time_off_requests.new_request') }}</h1>
    </div>

    <hr>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="flex flex-col gap-4 h-fit">
            <div class="card bg-base-300 ">
                <div class="card-body">
                    <h3 class="card-title">{{ __('time_off_requests.select_days') }}</h3>
                    <hr>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Data inizio</legend>
                        <input type="date" id="date_from" name="date_from" class="input w-full" />
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Data fine</legend>
                        <input type="date" id="date_to" name="date_to" class="input w-full" />
                    </fieldset>
                    <span class="text-error" id="date-error-field"></span>

                    <button class="btn btn-primary"
                        id="generate-days">{{ __('time_off_requests.time_off_generate_days') }}</button>


                    <button class="btn btn-secondary hidden"
                        id="set-as-leave">{{ __('time_off_requests.time_off_set_as_leave') }}</button>
                    <button class="btn btn-secondary hidden"
                        id="set-as-vacation">{{ __('time_off_requests.time_off_set_as_vacation') }}</button>

                    <button class="btn btn-primary hidden" id="submit-button">
                        {{ __('time_off_requests.submit') }}
                    </button>

                </div>
            </div>

            <div role="alert" class="alert alert-info">
                <x-lucide-info class="w-8 h-8" />
                <p>Una volta generate le date è necessario compilare gli orari di ogni singolo giorno</p>
            </div>

        </div>

        <div class="col-span-1 lg:col-span-3">
            <div class="card bg-base-300 " id="days-card">
                <div class="card-body">
                    <h3 class="card-title">{{ __('time_off_requests.new_request_days') }}</h3>
                    <hr>

                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th>{{ __('time_off_requests.new_request_type') }}</th>
                                    <th>{{ __('time_off_requests.new_request_day') }}</th>
                                    <th>{{ __('time_off_requests.new_request_start_time') }}</th>
                                    <th>{{ __('time_off_requests.new_request_end_time') }}</th>
                                    <th>{{ __('time_off_requests.new_request_total_hours') }}</th>
                                </tr>
                            </thead>
                            <tbody id="days-table-body">
                                <!-- Rows will be added here dynamically -->
                                <template id="days-table-row-template">
                                    <tr class="day-row" data-key="0">
                                        <td>
                                            <fieldset class="fieldset">
                                                <select class="select" name="type">
                                                    <option value="1">Ferie</option>
                                                    <option value="2">Rol</option>
                                                </select>
                                            </fieldset>
                                        </td>
                                        <td>
                                            <fieldset class="fieldset">
                                                <input type="date" name="day" class="input" disabled />
                                            </fieldset>
                                        </td>
                                        <td>
                                            <fieldset class="fieldset">
                                                <input type="time" name="start_time" class="input" />
                                            </fieldset>
                                        </td>
                                        <td>
                                            <fieldset class="fieldset">
                                                <input type="time" name="end_time" class="input" />
                                            </fieldset>
                                        </td>
                                        <td width="5%">
                                            <fieldset class="fieldset">
                                                <input type="number" name="total_hours" class="input" disabled />
                                            </fieldset>
                                        </td>

                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        @vite('resources/js/time_off_requests.js')
    @endpush

</x-layouts.app>
