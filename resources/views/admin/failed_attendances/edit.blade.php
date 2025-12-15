<x-layouts.app>

    <x-layouts.header :title="__('attendances.failed_attendance')" />

    <div class="card bg-base-300 w-full md:w-1/2 xl:w-1/3">
        <div class="card-body flex flex-col gap-2 ">



            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('attendances.name') }}</legend>
                <div class="p-2 bg-base-200 rounded">{{ $failedAttendance->user->name }}</div>
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('attendances.date') }}</legend>
                <div class="p-2 bg-base-200 rounded">
                    {{ \Carbon\Carbon::parse($failedAttendance->date)->format('d/m/Y') }}</div>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('attendances.request_type') }}</legend>
                <div class="p-2 bg-base-200 rounded">
                    {{ $failedAttendance->request_type == 0 ? 'Richiesta Permesso' : 'Richiesta Presenza' }}
                </div>
            </fieldset>

            @if ($failedAttendance->request_type == 0)
                <!-- Dettagli richiesta permesso -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('attendances.requested_hours') }}</legend>
                    <div class="p-2 bg-base-200 rounded">{{ $failedAttendance->requested_hours }}</div>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('attendances.requested_type') }}</legend>
                    <div class="p-2 bg-base-200 rounded">{{ $failedAttendance->requested_type == 0 ? 'ROL' : 'Ferie' }}
                    </div>
                </fieldset>
            @else
                <!-- Dettagli richiesta presenza -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Orari Mattino</legend>
                    <div class="p-2 bg-base-200 rounded">
                        Entrata: {{ $failedAttendance->requested_time_in_morning ?? 'Non specificato' }}<br>
                        Uscita: {{ $failedAttendance->requested_time_out_morning ?? 'Non specificato' }}
                    </div>
                </fieldset>

                @if ($failedAttendance->requested_time_in_afternoon || $failedAttendance->requested_time_out_afternoon)
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Orari Pomeriggio</legend>
                        <div class="p-2 bg-base-200 rounded">
                            Entrata: {{ $failedAttendance->requested_time_in_afternoon ?? 'Non specificato' }}<br>
                            Uscita: {{ $failedAttendance->requested_time_out_afternoon ?? 'Non specificato' }}
                        </div>
                    </fieldset>
                @endif
            @endif
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('attendances.justification') }}</legend>
                <div class="p-2 bg-base-200 rounded whitespace-pre-line">{{ $failedAttendance->reason }}</div>
            </fieldset>

            @if ($failedAttendance->request_type == 0)
                <!-- Pulsanti per richieste di permesso -->
                <div class="btn btn-primary" onclick="approve_rol_modal.showModal()">
                    {{ __('attendances.approve_rol') }}
                </div>

                <div class="btn btn-secondary" onclick="approve_time_off_modal.showModal()">
                    {{ __('attendances.approve_time_off') }}
                </div>
            @else
                <!-- Pulsante per richieste di presenza -->
                <div class="btn btn-success" onclick="approve_attendance_modal.showModal()">
                    Approva come Presenza
                </div>
            @endif

            <div class="btn btn-warning" onclick="deny_modal.showModal()">
                {{ __('attendances.deny') }}
            </div>


        </div>
    </div>


    <dialog class="modal" id="approve_rol_modal">
        <div class="modal-box">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold"> {{ __('attendances.approve_as_rol_title') }}</h3>
                <form method="dialog">
                    <button class="btn btn-ghost">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </form>
            </div>

            <p>{{ __('attendances.approve_as_rol_text') }}</p>

            <div class="modal-action">
                <form action="{{ route('admin.failed-attendances.approve', $failedAttendance) }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="rol">
                    <button type="submit" class="btn btn-primary">
                        {{ __('attendances.approve') }}
                    </button>
                </form>
                <button class="btn btn-ghost" onclick="approve_rol_modal.close()">
                    {{ __('attendances.cancel') }}
                </button>
            </div>
        </div>
    </dialog>

    <dialog class="modal" id="approve_time_off_modal">
        <div class="modal-box">

            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold"> {{ __('attendances.approve_as_time_off_title') }}</h3>
                <form method="dialog">
                    <button class="btn btn-ghost">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </form>
            </div>

            <p>{{ __('attendances.approve_as_time_off_text') }}</p>

            <div class="modal-action">
                <form action="{{ route('admin.failed-attendances.approve', $failedAttendance) }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="time_off">
                    <button type="submit" class="btn btn-primary">
                        {{ __('attendances.approve') }}
                    </button>
                </form>
                <button class="btn btn-ghost" onclick="approve_time_off_modal.close()">
                    {{ __('attendances.cancel') }}
                </button>
            </div>

        </div>
    </dialog>

    <dialog class="modal" id="deny_modal">
        <div class="modal-box">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold"> {{ __('attendances.deny_title') }}</h3>
                <form method="dialog">
                    <button class="btn btn-ghost">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </form>
            </div>

            <p>{{ __('attendances.deny_text') }}</p>

            <form action="{{ route('admin.failed-attendances.deny', $failedAttendance) }}" method="POST">
                @csrf
                <textarea name="reason" rows="4" class="textarea textarea-bordered w-full mt-2"
                    placeholder="{{ __('attendances.reason') }}"></textarea>
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">
                        {{ __('attendances.deny') }}
                    </button>
                    <button class="btn btn-ghost" onclick="deny_modal.close()">
                        {{ __('attendances.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Modal per approvazione presenza -->
    <dialog class="modal" id="approve_attendance_modal">
        <div class="modal-box">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold">Approva come Presenza</h3>
                <form method="dialog">
                    <button class="btn btn-ghost">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </form>
            </div>

            <p>Questa operazione creerà una nuova presenza con gli orari richiesti dall'utente.</p>

            <div class="modal-action">
                <form action="{{ route('admin.failed-attendances.approve', $failedAttendance) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        Approva Presenza
                    </button>
                </form>
                <button class="btn btn-ghost" onclick="approve_attendance_modal.close()">
                    {{ __('attendances.cancel') }}
                </button>
            </div>
        </div>
    </dialog>

</x-layouts.app>
