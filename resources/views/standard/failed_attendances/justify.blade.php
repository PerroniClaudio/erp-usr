<x-layouts.app>
    <x-layouts.header :title="__('attendances.failed_attendance')">
        <x-slot:actions>
            <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
                {{ __('attendances.submit_justification') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <form
        action="{{ route('failed-attendances.send-justification', [
            'failed_attendance' => $failedAttendance->id,
        ]) }}"
        method="POST" class="card bg-base-300">
        @csrf
        <div class="card-body">
            <p>{{ __('attendances.failed_explanation', [
                'date' => \Carbon\Carbon::parse($failedAttendance->date)->format('d/m/Y'),
            ]) }}
            </p>

            <div class="flex flex-col gap-2 w-1/3">

                <div class="mb-4">
                    <label for="reason"
                        class="block text-sm font-medium mb-1">{{ __('attendances.justification') }}</label>
                    <textarea id="reason" name="reason" class="textarea textarea-bordered w-full" rows="4" required>{{ $failedAttendance->reason }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="request_type" class="block text-sm font-medium mb-1">Tipo di richiesta</label>
                    <select id="request_type" name="request_type" class="select select-bordered w-full" required
                        onchange="toggleRequestType()">
                        <option disabled selected>Scegli il tipo di richiesta</option>
                        <option value="0">Richiesta Permesso</option>
                        <option value="1">Richiesta Presenza</option>
                    </select>
                </div>

                <!-- Sezione per richiesta permesso -->
                <div id="permission_section" class="hidden">
                    <div class="mb-4">
                        <label for="type"
                            class="block text-sm font-medium mb-1">{{ __('attendances.type') }}</label>
                        <select id="type" name="type" class="select select-bordered w-full">
                            <option disabled selected>Scegli</option>
                            <option value="0">ROL</option>
                            <option value="1">Ferie</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="lacking_hours"
                            class="block text-sm font-medium mb-1">{{ __('attendances.lacking_hours') }}</label>
                        <input id="lacking_hours" name="lacking_hours" type="text"
                            class="input input-bordered w-full" value="{{ $failedAttendance->requested_hours }}"
                            disabled>
                    </div>
                </div>

                <!-- Sezione per richiesta presenza -->
                <div id="attendance_section" class="hidden">
                    <div class="mb-4">
                        <h4 class="font-medium mb-2">Orari Mattino</h4>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label for="requested_time_in_morning"
                                    class="block text-sm font-medium mb-1">Entrata</label>
                                <input id="requested_time_in_morning" name="requested_time_in_morning" type="time"
                                    class="input input-bordered w-full">
                            </div>
                            <div class="flex-1">
                                <label for="requested_time_out_morning"
                                    class="block text-sm font-medium mb-1">Uscita</label>
                                <input id="requested_time_out_morning" name="requested_time_out_morning" type="time"
                                    class="input input-bordered w-full">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="font-medium mb-2">Orari Pomeriggio (opzionale)</h4>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label for="requested_time_in_afternoon"
                                    class="block text-sm font-medium mb-1">Entrata</label>
                                <input id="requested_time_in_afternoon" name="requested_time_in_afternoon"
                                    type="time" class="input input-bordered w-full">
                            </div>
                            <div class="flex-1">
                                <label for="requested_time_out_afternoon"
                                    class="block text-sm font-medium mb-1">Uscita</label>
                                <input id="requested_time_out_afternoon" name="requested_time_out_afternoon"
                                    type="time" class="input input-bordered w-full">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <script>
                function toggleRequestType() {
                    const requestType = document.getElementById('request_type').value;
                    const permissionSection = document.getElementById('permission_section');
                    const attendanceSection = document.getElementById('attendance_section');
                    const typeSelect = document.getElementById('type');
                    const timeInputs = ['requested_time_in_morning', 'requested_time_out_morning'];

                    if (requestType === '0') {
                        // Richiesta permesso
                        permissionSection.classList.remove('hidden');
                        attendanceSection.classList.add('hidden');
                        typeSelect.required = true;

                        // Rimuovi required dai campi orari
                        timeInputs.forEach(id => {
                            document.getElementById(id).required = false;
                        });
                    } else if (requestType === '1') {
                        // Richiesta presenza
                        permissionSection.classList.add('hidden');
                        attendanceSection.classList.remove('hidden');
                        typeSelect.required = false;

                        // Aggiungi required ai campi orari obbligatori
                        timeInputs.forEach(id => {
                            document.getElementById(id).required = true;
                        });
                    } else {
                        // Nessuna selezione
                        permissionSection.classList.add('hidden');
                        attendanceSection.classList.add('hidden');
                        typeSelect.required = false;

                        timeInputs.forEach(id => {
                            document.getElementById(id).required = false;
                        });
                    }
                }
            </script>


        </div>

        <button type="submit" id="submit-button" class="hidden"></button>
    </form>
</x-layouts.app>
