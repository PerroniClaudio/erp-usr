<x-layouts.app>

    <input type="hidden" name="batch_id" id="batch_id" value="{{ $batch_id }}">

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('time_off_requests.edit_request') }}</h1>
    </div>

    <hr>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

        <div class="flex flex-col gap-4 h-fit">
            <div class="card bg-base-300 ">
                <div class="card-body">
                    <h3 class="card-title">{{ __('time_off_requests.selected_days') }}</h3>
                    <hr>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Data inizio</legend>
                        <input type="date" id="date_from" name="date_from" class="input w-full"
                            value="{{ \Carbon\Carbon::parse($requests->first()->date_from)->format('Y-m-d') }}"
                            disabled />
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Data fine</legend>
                        <input type="date" id="date_to" name="date_to" class="input w-full"
                            value="{{ \Carbon\Carbon::parse($requests->last()->date_to)->format('Y-m-d') }}" disabled />
                    </fieldset>

                    <button class="btn btn-primary" id="edit-button">
                        {{ __('time_off_requests.submit') }}
                    </button>

                </div>
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
                                @foreach ($requests as $request)
                                    @continue($request->isInvalidDate())
                                    <tr class="day-row" data-key="{{ $request->id }}">
                                        <td>
                                            <fieldset class="fieldset">
                                                <select class="select" name="type" disabled>
                                                    @foreach ($types as $type)
                                                        <option value="{{ $type->id }}"
                                                            {{ $request->type->id == $type->id ? 'selected' : '' }}>
                                                            {{ $type->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </fieldset>
                                        </td>
                                        <td>
                                            <fieldset class="fieldset">
                                                <input type="date" name="day" class="input"
                                                    value="{{ \Carbon\Carbon::parse($request->date_from)->format('Y-m-d') }}"
                                                    disabled />
                                            </fieldset>
                                        </td>
                                        <td>
                                            <fieldset class="fieldset">
                                                <input type="time" name="start_time" class="input"
                                                    value="{{ \Carbon\Carbon::parse($request->date_from)->format('H:i') }}" />
                                            </fieldset>
                                        </td>
                                        <td>
                                            <fieldset class="fieldset">
                                                <input type="time" name="end_time" class="input"
                                                    value="{{ \Carbon\Carbon::parse($request->date_to)->format('H:i') }}" />
                                            </fieldset>
                                        </td>
                                        <td width="5%">
                                            <fieldset class="fieldset">
                                                <input type="number" name="total_hours" class="input"
                                                    value="{{ number_format(\Carbon\Carbon::parse($request->date_from)->diffInMinutes(\Carbon\Carbon::parse($request->date_to)) / 60, 2) }}"
                                                    disabled />
                                            </fieldset>
                                        </td>

                                    </tr>
                                @endforeach
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
