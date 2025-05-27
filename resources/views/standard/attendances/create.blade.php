<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('attendances.new_attendance') }}</h1>
        <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
            {{ __('attendances.save_attendance') }}
        </a>
    </div>

    <hr>

    <div class="card bg-base-300 ">
        <form class="card-body" method="POST" action="{{ route('attendances.store') }}">
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
                <legend class="fieldset-legend">Data presenza</legend>
                <input type="date" name="date" class="input"
                    value="{{ old('date', \Carbon\Carbon::today()->toDateString()) }}"
                    placeholder="{{ \Carbon\Carbon::today()->toDateString() }}" />

            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Orario inizio</legend>
                <input type="time" name="time_in" class="input" placeholder="00:00" value="{{ old('time_in') }}" />

            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Orario fine</legend>
                <input type="time" name="time_out" class="input" placeholder="00:00"
                    value="{{ old('time_out') }}" />

            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Tipo di presenza</legend>
                <select class="select" name="attendance_type_id" value="{{ old('attendance_type_id') }}">
                    @foreach ($attendanceTypes as $attendanceType)
                        <option value="{{ $attendanceType->id }}"
                            {{ old('attendance_type_id') == $attendanceType->id ? 'selected' : '' }}>
                            {{ $attendanceType->name }}
                        </option>
                    @endforeach
                </select>

            </fieldset>

            <button id="submit-button" type="submit" class="hidden">{{ __('attendances.save_attendance') }}</button>
        </form>
    </div>

</x-layouts.app>
