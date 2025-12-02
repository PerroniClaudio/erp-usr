<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Anomalie Orari - {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 15px;
            color: #333;
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
            color: #e73028;
        }

        .subtitle {
            font-size: 14px;
            margin: 10px 0;
            text-align: left;
        }

        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }

        .warning-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 5px;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            width: 25%;
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }

        .summary-cell.primary {
            background-color: #e73028;
            color: white;
        }

        .summary-cell.secondary {
            background-color: #437f97;
            color: white;
        }

        .summary-cell.warning {
            background-color: #ffc107;
            color: #212529;
        }

        .summary-cell.error {
            background-color: #dc3545;
            color: white;
        }

        .summary-label {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
        }

        .summary-detail {
            font-size: 8px;
            margin-top: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
        }

        td {
            font-size: 10px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 30px 0 10px 0;
            color: #495057;
            border-bottom: 2px solid #e73028;
            padding-bottom: 5px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }

        .badge.success {
            background-color: #28a745;
            color: white;
        }

        .badge.warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge.error {
            background-color: #dc3545;
            color: white;
        }

        .text-warning {
            color: #ffc107;
            font-weight: bold;
        }

        .text-error {
            color: #dc3545;
            font-weight: bold;
        }

        .text-success {
            color: #28a745;
            font-weight: bold;
        }

        .highlight-row {
            background-color: #f8d7da;
        }

        .page-break {
            page-break-before: always;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="company-info">IFORTECH S.R.L.</div>
        <div class="company-info">Via Esempio 123, 00100 Roma</div>
        <div class="company-info">Tel: +39 06 1234567 | Email: info@ifortech.it</div>
    </div>

    <!-- Title -->
    <div class="title">REPORT ANOMALIE ORARI</div>
    <div class="subtitle">
        <strong>Dipendente:</strong> {{ $user->name }}<br>
        <strong>Periodo:</strong> {{ $primoGiorno->format('d/m/Y') }} - {{ $ultimoGiorno->format('d/m/Y') }}
        ({{ $mese }} {{ $anno }} – {{ number_format($anomaliesData['totalExpectedHours'], 1) }}h previste su {{ $anomaliesData['workingDaysInPeriod'] }} giorni lavorativi)<br>
        <strong>Data generazione:</strong> {{ now()->format('d/m/Y H:i') }}
    </div>

    <!-- Warning Box -->
    <div class="warning-box">
        <div class="warning-title">Attenzione: Anomalie Rilevate</div>
        <div>Sono state rilevate delle anomalie negli orari di lavoro per il periodo specificato. Consultare i dettagli
            riportati di seguito.</div>
    </div>

    <!-- Summary -->
    <div class="summary-grid">
        <div class="summary-row">
            <div class="summary-cell primary">
                <div class="summary-label">ORE PREVISTE</div>
                <div class="summary-value">{{ number_format($anomaliesData['totalExpectedHours'], 1) }}</div>
                <div class="summary-detail">{{ $anomaliesData['workingDaysInPeriod'] }} giorni lavorativi</div>
            </div>
            <div class="summary-cell secondary">
                <div class="summary-label">ORE EFFETTIVE</div>
                <div class="summary-value">{{ number_format($anomaliesData['totalActualHours'], 1) }}</div>
            </div>
            <div class="summary-cell {{ $anomaliesData['totalDifference'] >= 0 ? 'warning' : 'error' }}">
                <div class="summary-label">DIFFERENZA</div>
                <div class="summary-value">
                    {{ $anomaliesData['totalDifference'] >= 0 ? '+' : '' }}{{ number_format($anomaliesData['totalDifference'], 1) }}
                </div>
                <div class="summary-detail">
                    {{ $anomaliesData['totalDifference'] >= 0 ? 'ore in eccesso' : 'ore mancanti' }}
                </div>
            </div>
            <div class="summary-cell error">
                <div class="summary-label">TIPO ANOMALIE</div>
                <div class="summary-value" style="font-size: 12px;">
                    @if ($anomaliesData['hasWeeklyAnomalies'] && $anomaliesData['hasMonthlyAnomalies'])
                        Settimanali e Mensili
                    @elseif($anomaliesData['hasWeeklyAnomalies'])
                        Settimanali
                    @elseif($anomaliesData['hasMonthlyAnomalies'])
                        Mensili
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Analysis -->
    <div class="section-title">ANALISI SETTIMANALE</div>
    <table>
        <thead>
            <tr>
                <th>Settimana</th>
                <th>Ore Previste</th>
                <th>Ore Effettive</th>
                <th>Differenza</th>
                <th>Stato</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($anomaliesData['weeklyData'] as $week)
                <tr
                    class="{{ $week['limit_exceeded'] || $week['has_shortage'] || $week['has_excess'] ? 'highlight-row' : '' }}">
                    <td><strong>{{ $week['week_start'] }} - {{ $week['week_end'] }}</strong></td>
                    <td>{{ number_format($week['expected_hours'], 1) }}h</td>
                    <td>{{ number_format($week['actual_hours'], 1) }}h</td>
                    <td>
                        <span class="{{ $week['difference'] >= 0 ? 'text-warning' : 'text-error' }}">
                            {{ $week['difference'] >= 0 ? '+' : '' }}{{ number_format($week['difference'], 1) }}h
                        </span>
                    </td>
                    <td>
                        @if ($week['limit_exceeded'])
                            <span class="badge error">Limite superato (>40h)</span>
                        @elseif($week['has_excess'])
                            <span class="badge warning">Ore in eccesso</span>
                        @elseif($week['has_shortage'])
                            <span class="badge error">Ore mancanti</span>
                        @else
                            <span class="badge success">Regolare</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Attendance Details -->
    <div class="section-title">DETTAGLIO PRESENZE</div>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Giorno</th>
                <th>Tipo</th>
                <th>Entrata</th>
                <th>Uscita</th>
                <th>Ore</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($anomaliesData['attendances']->sortBy('date') as $attendance)
                <tr>
                    <td><strong>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->locale('it')->isoFormat('dddd') }}</td>
                    <td>{{ $attendance->attendanceType->name }}</td>
                    <td>{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '-' }}
                    </td>
                    <td>{{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '-' }}
                    </td>
                    <td>
                        @if ($attendance->time_in && $attendance->time_out)
                            {{ number_format($attendance->signed_hours ?? (\Carbon\Carbon::parse($attendance->time_in)->diffInMinutes(\Carbon\Carbon::parse($attendance->time_out)) / 60), 1) }}h
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($anomaliesData['overtimeRequests']->count() > 0)
        <div class="section-title">STRAORDINARI</div>
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Data/Ora Inizio</th>
                    <th>Data/Ora Fine</th>
                    <th>Ore</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($anomaliesData['overtimeRequests']->sortBy('date') as $overtime)
                    <tr>
                        <td><strong>{{ $overtime->overtimeType->name ?? 'Straordinario' }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($overtime->date)->format('d/m/Y') }} {{ $overtime->time_in ? \Carbon\Carbon::parse($overtime->time_in)->format('H:i') : '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($overtime->date)->format('d/m/Y') }} {{ $overtime->time_out ? \Carbon\Carbon::parse($overtime->time_out)->format('H:i') : '-' }}</td>
                        <td>{{ number_format($overtime->hours ?? (\Carbon\Carbon::parse($overtime->time_in)->diffInMinutes(\Carbon\Carbon::parse($overtime->time_out)) / 60), 1) }}h</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Time Off Details -->
    @if ($anomaliesData['timeOffRequests']->count() > 0)
        <div class="section-title">PERMESSI E FERIE</div>
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Data/Ora Inizio</th>
                    <th>Data/Ora Fine</th>
                    <th>Ore</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($anomaliesData['timeOffRequests']->sortBy('date_from') as $timeOff)
                    <tr>
                        <td><strong>{{ $timeOff->type->name }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($timeOff->date_from)->format('d/m/Y H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($timeOff->date_to)->format('d/m/Y H:i') }}</td>
                        <td>{{ number_format(\Carbon\Carbon::parse($timeOff->date_from)->diffInMinutes(\Carbon\Carbon::parse($timeOff->date_to)) / 60, 1) }}h
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        Report generato automaticamente dal sistema ERP - {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
