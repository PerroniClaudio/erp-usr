<x-layouts.email>
    @slot('header')
        <h3>Richiesta di ferie/permesso</h3>
    @endslot

    @if ($isMailForAdmin)
        <p>L'utente {{ $user->name }} ha richiesto uno o più giorni di assenza. Di seguito i dettagli della richiesta:
        </p>
    @else
        <p>Hai richiesto uno o più giorni di assenza. Di seguito i dettagli della tua richiesta:</p>
    @endif

    <table class="table" style="margin-top:24px;">
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo richiesta</th>
                <th>Ora inizio</th>
                <th>Ora fine</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $request)
                <tr>
                    <td>{{ $request['date'] }}</td>
                    <td>{{ $request['type'] }}</td>
                    <td>{{ $request['date_from'] }}</td>
                    <td>{{ $request['date_to'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-layouts.email>
