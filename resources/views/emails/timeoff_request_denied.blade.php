<x-layouts.email>
    @slot('header')
        <h3>Richiesta di ferie/permesso rifiutata</h3>
    @endslot

    <p>Caro {{ $user->name }},</p>

    <p>
        La tua richiesta di assenza è stata <strong>rifiutata</strong>. Di seguito i dettagli:
    </p>

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

    <div style="margin-top: 24px; padding: 16px; background-color: #f5f5f5; border-left: 4px solid #dc2626;">
        <p style="margin: 0;"><strong>Motivazione del rifiuto:</strong></p>
        <p style="margin: 8px 0 0 0;">{{ $denialReason }}</p>
    </div>

    <p style="margin-top: 24px;">
        Se hai domande in merito, ti preghiamo di contattare l'amministrazione.
    </p>
</x-layouts.email>
