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

    <div class="title">Nota spese viaggi giornalieri - {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</div>
    <p>Il/La Sig./Sig.ra {{ $user->name }} ha effettuato i seguenti viaggi.</p>

    <table class="table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Azienda</th>
                <th>Km totali</th>
                <th>Costo km</th>
                <th>Valore economico</th>
                <th>Totale</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($travelsData as $data)
                <tr>
                    <td>{{ $data['travel']->travel_date?->format('d/m/Y') }}</td>
                    <td>{{ $data['travel']->company?->name }}</td>
                    <td>{{ number_format($data['distance'], 2, ',', '.') }}</td>
                    <td>€ {{ number_format($data['distance_cost'], 2, ',', '.') }}</td>
                    <td>€ {{ number_format($data['economic_value'], 2, ',', '.') }}</td>
                    <td>€ {{ number_format($data['total'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="title">Totali</div>
    <table class="table">
        <thead>
            <tr>
                <th>Km totali</th>
                <th>Costo km</th>
                <th>Valore economico</th>
                <th>Totale</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($totals['distance'], 2, ',', '.') }}</td>
                <td>€ {{ number_format($totals['distance_cost'], 2, ',', '.') }}</td>
                <td>€ {{ number_format($totals['economic_value'], 2, ',', '.') }}</td>
                <td>€ {{ number_format($totals['grand_total'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:20px"></div>
    <small>Le suindicate spese sono escluse da Irpef poiché rientrano nella fattispecie di cui al comma 8 , art. 50
        D.P.R. 22.12.1986, n. 917 e successive modifiche e integrazioni.</small>
</body>

</html>
