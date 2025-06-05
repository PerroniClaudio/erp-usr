<x-layouts.email>
    @slot('header')
        <h3>Negata giustificazione di mancata presenza</h3>
    @endslot

    <p>
        Si comunica che la mancata presenza dell'utente
        {{ str_replace(' - iFortech', '', $failedAttendance->user->name) }} in data
        {{ \Carbon\Carbon::parse($failedAttendance->date)->format('d/m/Y') }} non è stata approvata per la seguente
        motivazione:
    </p>

    <div class="email-riquadro">
        <p>
            {{ $reason }}
        </p>
    </div>



</x-layouts.email>
