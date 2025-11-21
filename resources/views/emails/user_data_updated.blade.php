<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Aggiornamento anagrafica</title>
</head>

<body>
    <p>
        {{ $isHrNotification ? 'Buongiorno team HR,' : 'Ciao ' . $subjectUser->name . ',' }}
    </p>

    <p>
        Le informazioni anagrafiche di <strong>{{ $subjectUser->name }}</strong>
        sono state aggiornate da {{ $performedBy?->name ?? 'un amministratore' }}.
    </p>

    @if (!empty($changes))
        <p>Campi modificati:</p>
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Valore precedente</th>
                    <th>Nuovo valore</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($changes as $change)
                    <tr>
                        <td>{{ $change['field'] }}</td>
                        <td>{{ $change['old'] ?? '—' }}</td>
                        <td>{{ $change['new'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Non sono stati rilevati dettagli sulle modifiche.</p>
    @endif

    <p>Grazie,<br>ERP Presenze</p>
</body>

</html>
