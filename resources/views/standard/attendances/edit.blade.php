<x-layouts.app>
    <x-layouts.header :title="__('attendances.edit_attendance')">
        <x-slot:actions>
            <div class="flex items-center gap-1">
                <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
                    {{ __('attendances.save_attendance') }}
                </a>

                <button class="btn btn-warning" onclick="delete_attendance.showModal()">
                    {{ __('attendances.delete_attendance') }}
                </button>

                <dialog id="delete_attendance" class="modal">
                    <div class="modal-box">
                        <div class="flex flex-row-reverse items-end">
                            <form method="dialog">
                                <!-- if there is a button in form, it will close the modal -->
                                <button class="btn btn-ghost">
                                    <x-lucide-x class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                        <h3 class="text-lg font-bold"> {{ __('attendances.delete_attendance') }}</h3>
                        <p class="py-4">
                            {{ __('attendances.delete_attendance_confirmation') }}
                        </p>
                        <form method="POST"
                            action="{{ route('attendances.destroy', ['attendance' => $attendance->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-warning">
                                {{ __('attendances.delete_attendance') }}
                            </button>
                        </form>

                    </div>
                </dialog>
            </div>
        </x-slot:actions>
    </x-layouts.header>

    <div class="card bg-base-200 ">
        <form class="card-body" method="POST"
            action="{{ route('attendances.update', [
                'attendance' => $attendance->id,
            ]) }}">
            @csrf
            @method('PUT')

            @if (Auth::user()->hasRole('admin'))
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Utente</legend>
                    <input type="text" class="input" value="{{ $attendance->user->name }}" disabled />
                </fieldset>
            @endif

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Azienda</legend>
                <select class="select" name="company_id" value="{{ $attendance->company_id }}">
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}"
                            {{ $attendance->company_id == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>

            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Data presenza</legend>
                <input type="date" name="date" class="input" value="{{ optional($attendance->date)->toDateString() }}"
                    placeholder="{{ \Carbon\Carbon::today()->toDateString() }}" />

            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Orario inizio</legend>
                <input type="time" name="time_in" class="input" placeholder="00:00"
                    value="{{ $attendance->time_in }}" />

            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Orario fine</legend>
                <input type="time" name="time_out" class="input" placeholder="00:00"
                    value="{{ $attendance->time_out }}" />

            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Tipo di presenza</legend>
                <select class="select" name="attendance_type_id" value="{{ $attendance->attendance_type_id }}">
                    @foreach ($attendanceTypes as $attendanceType)
                        <option value="{{ $attendanceType->id }}"
                            {{ $attendance->attendance_type_id == $attendanceType->id ? 'selected' : '' }}>
                            {{ $attendanceType->name }}
                        </option>
                    @endforeach
                </select>

            </fieldset>

            <button id="submit-button" type="submit" class="hidden">{{ __('attendances.save_attendance') }}</button>
        </form>
    </div>

</x-layouts.app>
