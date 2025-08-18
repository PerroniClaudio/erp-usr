<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Esportazione presenze {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: right;
            margin-bottom: 20px;
            position: relative;
        }

        .logo {
            position: absolute;
            left: 0;
            top: 0;
            max-width: 200px;
        }

        .company-info {
            margin-bottom: 5px;
            font-size: 10px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-align: left;
        }

        .subtitle {
            font-size: 14px;
            margin: 10px 0;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .summary-table th {
            background-color: #f0f0f0;
        }

        .summary-table td {
            text-align: center;
        }

        .details-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .details-table tr.ferie {
            background-color: #FFFFCC;
        }

        .page-number {
            text-align: right;
            font-size: 10px;
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        .buoni-pasto {
            margin: 10px 0;
            border: 1px solid #000;
        }

        .buoni-pasto td {
            padding: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" class="logo" alt="IFORTECH S.R.L.">
        <div class="company-info">IFORTECH S.R.L.</div>
        <div class="company-info">Via Pisa, 250 - Sesto San Giovanni - Milano - 20099</div>
        <div class="company-info">Strada 4 palazzo Q5 – 3° piano, Rozzano - Milano - 20089</div>
        <div class="company-info">E-mail: info@ifortech.com / Web: www.ifortech.com</div>
        <div class="company-info">P. IVA 07927140967 / Cod. fiscale: 07927140967</div>
    </div>

    <div class="title">Esportazione presenze {{ $user->name }}</div>
    <div class="subtitle">Mese: {{ $mese }}</div>
    <div class="subtitle">Anno: {{ $anno }}</div>

    <!-- Tabella riassuntiva -->
    <table class="summary-table">
        <thead>
            <tr>
                <th>LAVORATO</th>
                <th>STRAORDINARIO</th>
                <th>STRAORD.<br>NOTTURNO</th>
                <th>STRAORD.<br>FESTIVO</th>
                <th>ROL</th>
                <th>FERIE</th>
                <th>CORSO INTRA<br>(incluse in<br>lavorato)</th>
                <th>CORSO EXTRA</th>
                <th>MALATTIA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $riepilogo['lavorato']['ore'] }}</td>
                <td>{{ $riepilogo['straordinario']['ore'] }}</td>
                <td>{{ $riepilogo['straordinario_notturno']['ore'] }}</td>
                <td>{{ $riepilogo['straordinario_festivo']['ore'] }}</td>
                <td>{{ $riepilogo['rol']['ore'] }}</td>
                <td>{{ $riepilogo['ferie']['ore'] }}</td>
                <td>{{ $riepilogo['corso_intra']['ore'] }}</td>
                <td>{{ $riepilogo['corso_extra']['ore'] }}</td>
                <td>{{ $riepilogo['malattia']['ore'] }}</td>
            </tr>
            <tr>
                <td>{{ number_format($riepilogo['lavorato']['giorni'], 3) }}</td>
                <td>{{ number_format($riepilogo['straordinario']['giorni'], 3) }}</td>
                <td>{{ number_format($riepilogo['straordinario_notturno']['giorni'], 3) }}</td>
                <td>{{ number_format($riepilogo['straordinario_festivo']['giorni'], 3) }}</td>
                <td>{{ number_format($riepilogo['rol']['giorni'], 3) }}</td>
                <td>{{ number_format($riepilogo['ferie']['giorni'], 3) }}</td>
                <td>{{ number_format($riepilogo['corso_intra']['giorni'], 3) }}</td>
                <td>{{ number_format($riepilogo['corso_extra']['giorni'], 3) }}</td>
                <td>{{ number_format($riepilogo['malattia']['giorni'], 3) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Tabella buoni pasto -->
    <table class="buoni-pasto">
        <tr>
            <td style="width: 50%; text-align: left; border-right: 1px solid #000;">Buoni pasto maturati</td>
            <td style="width: 50%; text-align: right;">{{ $buoni_pasto }}</td>
        </tr>
    </table>

    <!-- Tabella dettagli presenze -->
    <table class="details-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>PERSONA</th>
                <th>AZIENDA</th>
                <th>TIPOLOGIA</th>
                <th>DATA</th>
                <th>ORA INIZIO</th>
                <th>ORA FINE</th>
                <th>ORE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($presenze as $presenza)
                <tr class="{{ strtolower($presenza->tipologia) == 'ferie' ? 'ferie' : '' }}">
                    <td>{{ $presenza->id }}</td>
                    <td>{{ $presenza->persona }}</td>
                    <td>{{ $presenza->azienda }}</td>
                    <td>{{ $presenza->tipologia }}</td>
                    <td>{{ $presenza->data_formattata }}</td>
                    <td>{{ $presenza->ora_inizio }}</td>
                    <td>{{ $presenza->ora_fine }}</td>
                    <td>{{ $presenza->ore }} {{ $presenza->annullata ? 'X' : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-number">{{ $pagina }} / {{ $totale_pagine }}</div>
</body>

</html>
