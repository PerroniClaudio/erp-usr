<x-layouts.app>
    <x-layouts.header :title="__('attendances.new_attendance')">
        <x-slot:actions>
            <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
                {{ __('attendances.save_attendance') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <div class="grid md:grid-cols-2 gap-4">

        <div class="card bg-base-300">
            <form class="card-body gap-4" method="POST" action="{{ route('attendances.store') }}">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if (auth()->user()->hasRole('admin'))
                        <fieldset class="fieldset sm:col-span-2">
                            <legend class="fieldset-legend">Utente</legend>
                            <select class="select w-full" name="user_id" value="{{ old('user_id') }}">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </fieldset>
                    @endif

                    <fieldset class="fieldset sm:col-span-2">
                        <legend class="fieldset-legend">Azienda</legend>
                        <select class="select w-full" name="company_id" value="{{ old('company_id') }}">
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>

                    <fieldset class="fieldset sm:col-span-2">
                        <legend class="fieldset-legend">Data presenza</legend>
                        <input type="date" name="date" class="input w-full"
                            value="{{ old('date', \Carbon\Carbon::today()->toDateString()) }}"
                            placeholder="{{ \Carbon\Carbon::today()->toDateString() }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Orario inizio</legend>
                        <input type="time" name="time_in" class="input w-full" placeholder="00:00"
                            value="{{ old('time_in') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Orario fine</legend>
                        <input type="time" name="time_out" class="input w-full" placeholder="00:00"
                            value="{{ old('time_out') }}" />
                    </fieldset>

                    <fieldset class="fieldset sm:col-span-2">
                        <legend class="fieldset-legend">Tipo di presenza</legend>
                        <select class="select w-full" name="attendance_type_id" value="{{ old('attendance_type_id') }}">
                            @foreach ($attendanceTypes as $attendanceType)
                                <option value="{{ $attendanceType->id }}"
                                    {{ old('attendance_type_id') == $attendanceType->id ? 'selected' : '' }}>
                                    {{ $attendanceType->name }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>
                </div>

                <button id="submit-button" type="submit"
                    class="hidden">{{ __('attendances.save_attendance') }}</button>
            </form>
        </div>

        @unless (auth()->user()->hasRole('admin'))
            <div class="card bg-base-200">
                <div class="card-body" data-expected-schedule data-fetch-url="{{ route('attendances.scheduled-slots') }}"
                    data-empty-label="{{ __('attendances.expected_schedule_empty') }}"
                    data-initial-schedule='@json($expectedSchedule)' data-fallback-color="#94a3b8">
                    <h2 class="card-title">{{ __('attendances.expected_schedule_title') }}</h2>
                    <div class="text-sm text-base-content/70">
                        <span>{{ __('attendances.expected_schedule_description') }}</span>
                    </div>
                    <ul class="text-sm space-y-2" data-expected-schedule-list>
                        @forelse ($expectedSchedule as $slot)
                            <li class="flex items-center justify-between rounded-lg border border-base-200 px-2 py-1">
                                <div class="flex items-center gap-2 text-xs uppercase text-base-content/60">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full border border-base-300"
                                        style="background-color: {{ $slot['attendance_type']['color'] ?? '#94a3b8' }};"></span>
                                    {{ $slot['attendance_type']['acronym'] ?? ($slot['attendance_type']['name'] ?? '-') }}
                                </div>
                                <span class="font-medium">{{ $slot['hour_start'] }} - {{ $slot['hour_end'] }}</span>
                            </li>
                        @empty
                            <li class="text-xs text-base-content/60">
                                {{ __('attendances.expected_schedule_empty') }}
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endunless

    </div>




    @push('scripts')
        @vite('resources/js/attendance-create.js')
    @endpush

</x-layouts.app>
