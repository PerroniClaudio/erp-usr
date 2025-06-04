<x-layouts.email>
    @slot('header')
        <h3>Anomalie nelle presenze</h3>
    @endslot

    <p>Nella giornata odierna i seguenti utenti hanno avuto anomalie con le presenze:</p>
    <table class="table" style="margin-top:24px;">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Ore registrate
            </tr>
        </thead>
        <tbody>
            @foreach ($reportData as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['total_hours'] }} ore</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p>Gli utenti verranno avvisati e richiederanno le ore mancanti come ROL o Ferie.</p>
</x-layouts.email>
