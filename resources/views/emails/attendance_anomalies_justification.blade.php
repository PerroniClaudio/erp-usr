<x-layouts.email>
    @slot('header')
        <h3>Giustificazione di mancata presenza</h3>
    @endslot

    <p>
        L'utente {{ str_replace(' - iFortech', '', $failedAttendance->user->name) }} giustifica la mancata presenza del
        giorno
        {{ \Carbon\Carbon::parse($failedAttendance->date)->format('d/m/Y') }}, richiedendo
        {{ $failedAttendance->requested_hours }} ore di {{ $failedAttendance->requested_type == 0 ? 'ROL' : 'Ferie' }}.
    </p>

    <p>Con la seguente giustificazione: {{ $failedAttendance->reason }}</p>

</x-layouts.email>
