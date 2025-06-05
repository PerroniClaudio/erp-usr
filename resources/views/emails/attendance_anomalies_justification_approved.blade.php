<x-layouts.email>
    @slot('header')
        <h3>Approvazione giustificazione di mancata presenza</h3>
    @endslot

    <p>
        Si comunica che la mancata presenza dell'utente
        {{ str_replace(' - iFortech', '', $failedAttendance->user->name) }} in data
        {{ \Carbon\Carbon::parse($failedAttendance->date)->format('d/m/Y') }}
        è stata approvata conferendo ore {{ $failedAttendance->requested_hours }} di
        {{ $timeOffType }}.
    </p>


</x-layouts.email>
