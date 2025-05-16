<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cedolino {{ $mese }} {{ $anno }} - {{ $user->name }}</title>
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
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .legend-column {
            width: 48%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        #days-table th,
        #days-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #d4d4d8;
        }

        .bg-gray {
            background-color: #d4d4d8;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-info">IFORTECH S.R.L.</div>
        <div class="company-info">Via Pisa, 250 - Sesto San Giovanni - Milano - 20099</div>
        <div class="company-info">Strada 4 palazzo Q5 – 3° piano, Rozzano - Milano - 20089</div>
        <div class="company-info">E-mail: info@ifortech.com / Web: www.ifortech.com</div>
        <div class="company-info">P. IVA 07927140967 / Cod. fiscale: 07927140967</div>
    </div>

    <div class="title">Cedolino - Mese {{ $mese }} {{ $anno }}</div>
    <p>Nominativo: {{ $user->name }}</p>

    <table>
        <tbody>
            <tr>
                <td style="width: 50%;border: 2px solid #000;padding: 5px">
                    <p>LAVORATIVO => LA</p>
                    <p>FERIE => FE</p>
                    <p>MALATTIA => MA</p>
                    <p>ROL => ROL</p>
                    <p>LICENZA MATRIMONIO => LM</p>
                    <p>SMART WORKING => SW</p>
                </td>
                <td style="width: 50%;border: 2px solid #000;padding: 5px">
                    <p>STRAORDINARIO => ST</p>
                    <p>STRAORDINARIO NOTTURNO => STN</p>
                    <p>STRAORDINARIO FESTIVO => STF</p>
                    <p>STRAORDINARIO NOTTURNO/FESTIVO => STNF</p>
                    <p>ORE VIAGGIO => OV</p>
                </td>
            </tr>
        </tbody>
    </table>

    <table id="days-table">
        <thead>
            <tr>
                <th>TIPOLOGIA</th>
                @php
                    $numGiorni = cal_days_in_month(CAL_GREGORIAN, $meseNumero, $anno);
                @endphp

                @for ($i = 1; $i <= $numGiorni; $i++)
                    @php
                        $data = \Carbon\Carbon::createFromDate($anno, $meseNumero, $i);
                        $dayLetter = strtoupper(substr($data->locale('it')->dayName, 0, 1));
                    @endphp
                    <th>{{ $dayLetter }}<br>{{ $i }}</th>
                @endfor
                <th>TOT ORE</th>
                <th>TOT GIORNI</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tipiAttivita = [
                    'LS' => 'LAVORO IN SEDE',
                    'SW' => 'SMART WORKING',
                    'FE' => 'FERIE',
                    'MA' => 'MALATTIA',
                    'ROL' => 'ROL',
                    'ST' => 'STRAORDINARIO',
                    'STN' => 'STRAORDINARIO NOTTURNO',
                    'STF' => 'STRAORDINARIO FESTIVO',
                    'STNF' => 'STRAORDINARIO NOTTURNO/FESTIVO',
                    'LM' => 'LICENZA MATRIMONIO',
                    'OV' => 'ORE VIAGGIO',
                ];

                $datiGiorni = [];
                foreach ($tipiAttivita as $codice => $descrizione) {
                    $datiGiorni[$codice] = array_fill(1, $numGiorni, null);
                }

                $attendances = \App\Models\Attendance::where('user_id', $user->id)
                    ->whereBetween('date', [$primoGiorno, $ultimoGiorno])
                    ->get();

                $timeOffRequests = \App\Models\TimeOffRequest::where('user_id', $user->id)
                    ->where(function ($query) use ($primoGiorno, $ultimoGiorno) {
                        $query
                            ->whereBetween('date_from', [$primoGiorno, $ultimoGiorno])
                            ->orWhereBetween('date_to', [$primoGiorno, $ultimoGiorno]);
                    })
                    ->get();

                foreach ($attendances as $attendance) {
                    $giorno = \Carbon\Carbon::parse($attendance->date)->day;
                    $datiGiorni[$attendance->attendanceType->acronym][$giorno] = $attendance->hours;
                }

                foreach ($timeOffRequests as $request) {
                    $startDate = \Carbon\Carbon::parse($request->date_from);
                    $endDate = \Carbon\Carbon::parse($request->date_to);

                    for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                        if ($date->month == $meseNumero && $date->year == $anno) {
                            $giorno = $date->day;

                            $date_from = \Carbon\Carbon::parse($request->date_from);
                            $date_to = \Carbon\Carbon::parse($request->date_to);
                            $difference = $date_from->diffInHours($date_to);

                            switch ($request->type->name) {
                                case 'Rol':
                                    $datiGiorni['ROL'][$giorno] = $difference;
                                    break;
                                case 'Ferie':
                                    $datiGiorni['FE'][$giorno] = $difference;
                                    break;
                            }
                        }
                    }
                }

                $allSaturdaysBetween = \Carbon\Carbon::parse($primoGiorno)
                    ->daysUntil(\Carbon\Carbon::parse($ultimoGiorno))
                    ->filter(function ($day) {
                        return $day->isSaturday();
                    });
                $allSundaysBetween = \Carbon\Carbon::parse($primoGiorno)
                    ->daysUntil(\Carbon\Carbon::parse($ultimoGiorno))
                    ->filter(function ($day) {
                        return $day->isSunday();
                    });

            @endphp

            @foreach ($tipiAttivita as $codice => $descrizione)
                <tr>
                    <td>{{ $codice }}</td>

                    @php
                        $totOre = 0;
                        $totGiorni = 0;
                    @endphp

                    @for ($i = 1; $i <= $numGiorni; $i++)
                        @php
                            $ore = $datiGiorni[$codice][$i];
                            if ($ore) {
                                $totOre += $ore;
                                $totGiorni += $ore / 8;
                            }
                            $data = \Carbon\Carbon::createFromDate($anno, $meseNumero, $i);
                            $isWeekend = $data->isSaturday() || $data->isSunday();
                        @endphp
                        <td class="{{ $isWeekend ? 'bg-gray' : '' }}">{{ $ore }}</td>
                    @endfor

                    <td>{{ $totOre }}</td>
                    <td>{{ number_format($totGiorni, 1) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
