<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Nota spese - {{ $dailyTravel->user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .header {
            text-align: right;
            margin-bottom: 20px;
        }

        .company-info {
            margin-bottom: 5px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #d4d4d8;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f8fafc;
        }

        .card {
            border: 1px solid #d4d4d8;
            padding: 6px;
            background: #f8fafc;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-info">IFORTECH S.R.L.</div>
        <div class="company-info">Via Pisa, 250 - Sesto San Giovanni - Milano - 20099</div>
        <div class="company-info">E-mail: info@ifortech.com / Web: www.ifortech.com</div>
        <div class="company-info">P. IVA 07927140967 / Cod. fiscale: 07927140967</div>
    </div>

    <hr>

    <div class="title">Nota spese viaggio giornaliero</div>

    <p>Il/La Sig./Sig.ra {{ $dailyTravel->user->name }} ha effettuato il viaggio del
        {{ $dailyTravel->travel_date?->format('d/m/Y') }}.</p>

    <table style="margin-top: 10px;">
        <tr>
            <td style="width: 50%;" class="card">
                <p><b>Veicolo:</b></p>
                <p>{{ $structure?->vehicle?->brand }} {{ $structure?->vehicle?->model }}</p>
                <ul>
                    <li>Prezzo/km: € {{ number_format((float) $structure?->cost_per_km, 4, ',', '.') }}</li>
                    <li>Valore economico: € {{ number_format((float) $structure?->economic_value, 2, ',', '.') }}</li>
                </ul>
            </td>
            <td style="width: 50%;" class="card">
                <p><b>Nota spese n°: </b>{{ $dailyTravel->id }}</p>
                <p><b>Data documento: </b>{{ \Carbon\Carbon::parse($document_date)->format('d/m/Y') }}</p>
                <p><b>Km totali: </b>{{ number_format($totalDistance, 2, ',', '.') }}</p>
                <p><b>Costo km: </b>€ {{ number_format($totalDistanceCost, 2, ',', '.') }}</p>
            </td>
        </tr>
    </table>

    <div class="title">Percorso</div>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Indirizzo</th>
                <th>Città</th>
                <th>Provincia</th>
                <th>CAP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($steps as $step)
                <tr>
                    <td>{{ $step->step_number }}</td>
                    <td>{{ $step->address }}</td>
                    <td>{{ $step->city }}</td>
                    <td>{{ $step->province }}</td>
                    <td>{{ $step->zip_code }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="title">Distanze</div>
    <table class="table">
        <thead>
            <tr>
                <th>Da</th>
                <th>A</th>
                <th>Distanza (km)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($distancesBetweenSteps as $distance)
                <tr>
                    <td>{{ $distance['from']->city }} - {{ $distance['from']->address }}</td>
                    <td>{{ $distance['to']->city }} - {{ $distance['to']->address }}</td>
                    <td>{{ number_format($distance['distance'], 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Nessuna distanza calcolata.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="title">Spese</div>
    <table class="table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Luogo</th>
                <th>Tipo spesa</th>
                <th>Tipo pagamento</th>
                <th>Importo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($expenses as $expense)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                    <td>{{ $expense->city }}</td>
                    <td>{{ $expense->expenseType() }}</td>
                    <td>{{ $expense->paymentType() }}</td>
                    <td>€ {{ number_format($expense->amount, 2, ',', '.') }} </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Nessuna spesa registrata.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="title">Totale</div>
    <table class="table" style="margin-top: 10px;">
        <thead>
            <tr>
                <th></th>
                <th>Spese</th>
                <th>Km</th>
                <th>Valore economico</th>
                <th>Totale</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Totali</td>
                <td>€ {{ number_format($totalExpenses, 2, ',', '.') }}</td>
                <td>€ {{ number_format($totalDistanceCost, 2, ',', '.') }}</td>
                <td>€ {{ number_format((float) $structure?->economic_value, 2, ',', '.') }}</td>
                <td>€ {{ number_format($grandTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:20px"></div>
    <small>Le suindicate spese sono escluse da Irpef poiché rientrano nella fattispecie di cui al comma 8 , art. 50
        D.P.R. 22.12.1986, n. 917 e successive modifiche e integrazioni.</small>
</body>

</html>
