<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Nota spese - {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .header {
            text-align: right;
            margin-bottom: 20px;
        }

        .logo {
            max-width: 200px;
            margin-bottom: 10px;
        }

        .company-info {
            margin-bottom: 5px;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            margin: 20px 0;
        }

        .legend {

            margin-top: 20px;
        }



        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #d4d4d8;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #f8fafc;
        }

        .bg-gray {
            background-color: #d4d4d8;
        }

        .card {
            border: 1px solid #d4d4d8;

            padding-top: 4px;
            padding-left: 8px;
            background: #f8fafc;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
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

    <p>Il/La Sig./Sig.ra {{ $user->name }} è autorizzato/a ad effettuare il viaggio utilizzando il proprio
        automezzo nei termini indicati nel seguente riquadro.</p>

    <table>
        <tr>
            <td style="width: 50%;" class="card">
                <p><b>Automezzo:</b></p>
                <p>{{ $user_vehicle->model }}</p>
                <ul>
                    <li>Marca: {{ $user_vehicle->brand }}</li>
                    <li>Tipo: {{ $user_vehicle->pivot->vehicle_type }}</li>
                    <li>Targa: {{ $user_vehicle->pivot->plate_number }}</li>
                </ul>
            </td>
            <td style="width: 50%;" class="card">

                <p><b>Nota spese n°: </b>{{ $month }}</p>
                <p><b>Del: </b>{{ $month }}/{{ $year }}</p>

            </td>
        </tr>
    </table>

    {{-- Trasferimenti --}}

    <div class="title">Trasferimenti</div>

    <table class="table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Luogo di partenza</th>
                <th>Luogo di arrivo</th>
                <th>Prezzo al Km</th>
                <th>Km percorsi</th>
                <th>Totale</th>
            </tr>
        </thead>
        <tbody>

            @php

                $transfers = collect($allTripsData)
                    ->flatMap(function ($trip) {
                        return $trip['transfers'] ?? [];
                    })
                    ->values();

            @endphp


            @foreach ($transfers as $transfer)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($transfer['from']->date)->format('d/m/Y') }}</td>
                    <td>{{ $transfer['from']->city }}</td>
                    <td>{{ $transfer['to']->city }}</td>
                    <td>€ {{ number_format($transfer['ekm'], 2, ',', '.') }} </td>
                    <td>{{ number_format($transfer['distance'], 2, ',', '.') }} Km</td>
                    <td>€ {{ number_format($transfer['total'], 2, ',', '.') }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Spese --}}

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

            @php

                $expenses = collect($allTripsData)
                    ->flatMap(function ($trip) {
                        return $trip['expenses'] ?? [];
                    })
                    ->values();

            @endphp

            @foreach ($expenses as $expense)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                    <td>{{ $expense->city }}</td>
                    <td>{{ $expense->expenseType() }}</td>
                    <td>{{ $expense->paymentType() }}</td>
                    <td>€ {{ number_format($expense->amount, 2, ',', '.') }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="title">Totale</div>

    @php
        $totalExpenses = $expenses->sum('amount');
        $totalTransfers = collect($transfers)->sum('total');
        $bigtotal = $totalExpenses + $totalTransfers;
    @endphp

    <table class="table" style="margin-top: 20px;">
        <thead>
            <tr>
                <th></th>
                <th>Spese</th>
                <th>Trasferimenti</th>
                <th>Totale</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Totali</td>
                <td>€ {{ number_format($totalExpenses, 2, ',', '.') }} </td>
                <td>€ {{ number_format($totalTransfers, 2, ',', '.') }} </td>
                <td>€ {{ number_format($bigtotal, 2, ',', '.') }} </td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:20px"></div>

    <small>Le suindicate spese sono escluse da Irpef poiché rientrano nella fattispecie di cui al comma 8 , art. 50
        D.P.R. 22.12.1986, n. 917 e successive modifiche e integrazioni.</small>

    <div class="card" style="margin-top: 20px; padding: 4px;">
        <p>Marca da bollo 1.81 € se l'importo supera € 77.46</p>
    </div>

    <table style="width: 100%; margin-top: 40px;">
        <tr>
            <td style="width: 50%; text-align: left;">
                <b>Data:</b>
                <div style="border-bottom: 1px solid #000; width: 80%; height: 24px; display: inline-block;"></div>
            </td>
            <td style="width: 50%; text-align: right;">
                <b>Firma:</b>
                <div style="border-bottom: 1px solid #000; width: 80%; height: 24px; display: inline-block;"></div>
            </td>
        </tr>
    </table>

</body>
