<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <table>
        <thead>
            <tr>
                <th>mese</th>
                <th>ore ferie</th>
                <th>ore rol</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mesi as $mese => $ore)
                <tr>
                    <td>{{ $ore['month'] }}</td>
                    <td>{{ $ore['hours_off'] }}</td>
                    <td>{{ $ore['hours_rol'] }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>

    <p>
        Totale ore ferie: {{ $totalFerie }} <br>
        Totale ore rol: {{ $totalRol }}
    </p>

</body>

</html>
