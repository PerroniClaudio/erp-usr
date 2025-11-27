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

            margin-top: 20px;
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

        .bg-gray-darker {
            background-color: #a1a1aa;
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

    <div class="title">Cedolino - Mese {{ $mese }} {{ $anno }}</div>
    <p>Nominativo: {{ $user->name }}</p>



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
                        $isWeekend = $data->isSaturday() || $data->isSunday();
                        $isFestive = in_array($data->format('m-d'), $festive);
                    @endphp
                    <th class="{{ $isFestive ? 'bg-gray-darker' : ($isWeekend ? 'bg-gray' : '') }}">
                        {{ $dayLetter }}<br>{{ $i }}
                    </th>
                @endfor

                <th>TOT ORE</th>
                <th>TOT GIORNI</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tipiAttivita = [
                    'LS' => 'LAVORO IN SEDE',
                    'LC' => 'Lavoro c/o cliente',
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
                    'CIL' => 'CORSO INTRA-LAVORATIVO',
                    'CP' => 'CONGEDO PARENTALE',
                    'L104' => 'PERMESSO LEGGE 104',
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

                $overtimeRequests = \App\Models\OvertimeRequest::with('overtimeType')
                    ->where('user_id', $user->id)
                    ->whereBetween('date', [$primoGiorno, $ultimoGiorno])
                    ->where('status', 2)
                    ->get();


                foreach ($attendances as $attendance) {
                    $giorno = \Carbon\Carbon::parse($attendance->date)->day;
                    if (is_null($datiGiorni[$attendance->attendanceType->acronym][$giorno])) {
                        $datiGiorni[$attendance->attendanceType->acronym][$giorno] = 0;
                    }
                    $datiGiorni[$attendance->attendanceType->acronym][$giorno] += $attendance->hours;
                }

                // Straordinari: ST, STN, STF, STNF
                foreach ($overtimeRequests as $overtime) {
                    $giorno = \Carbon\Carbon::parse($overtime->date)->day;
                    $acronym = $overtime->overtimeType->acronym ?? null;
             
                    if (
                        $acronym &&
                        array_key_exists($acronym, $datiGiorni) &&
                        array_key_exists($giorno, $datiGiorni[$acronym])
                    ) {
                        if (is_null($datiGiorni[$acronym][$giorno])) {
                            $datiGiorni[$acronym][$giorno] = 0;
                        }
                        $datiGiorni[$acronym][$giorno] += $overtime->hours;
                    }
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
                                    if (is_null($datiGiorni['ROL'][$giorno])) {
                                        $datiGiorni['ROL'][$giorno] = 0;
                                    }
                                    $datiGiorni['ROL'][$giorno] += $difference;
                                    break;
                                case 'Ferie':
                                    if (is_null($datiGiorni['FE'][$giorno])) {
                                        $datiGiorni['FE'][$giorno] = 0;
                                    }
                                    $datiGiorni['FE'][$giorno] += $difference;
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
                            $isFestive = in_array($data->format('m-d'), $festive);
                        @endphp
                        <td class="{{ $isFestive ? 'bg-gray-darker' : ($isWeekend ? 'bg-gray' : '') }}">
                            {{ $ore }}</td>
                    @endfor

                    <td>{{ $totOre }}</td>
                    <td>{{ number_format($totGiorni, 1) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div
        style="border: 1px solid #d4d4d8; margin-top: 20px; padding: 16px; background: #f8fafc; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0 0;">
            <tr>
                <td style="width: 20%">
                    <ul style="list-style: none; padding-left: 0; margin: 0;">
                        <li><strong>LS</strong> - LAVORO IN SEDE</li>
                        <li><strong>LC</strong> - LAVORO C/O CLIENTE</li>
                        <li><strong>SW</strong> - SMART WORKING</li>
                    </ul>
                </td>
                <td style="width: 20%">
                    <ul style="list-style: none; padding-left: 0; margin: 0;">
                        <li><strong>ROL</strong> - ROL</li>
                        <li><strong>FE</strong> - FERIE</li>
                    </ul>
                </td>
                <td style="width: 20%">
                    <ul style="list-style: none; padding-left: 0; margin: 0;">
                        <li><strong>MA</strong> - MALATTIA</li>
                        <li><strong>LM</strong> - LICENZA MATRIMONIO</li>
                        <li><strong>CP</strong> - CONGEDO PARENTALE</li>
                        <li><strong>L104</strong> - PERMESSO LEGGE 104</li>

                    </ul>
                </td>
                <td style="width: 20%">
                    <ul style="list-style: none; padding-left: 0; margin: 0;">
                        <li><strong>ST</strong> - STRAORDINARIO</li>
                        <li><strong>STN</strong> - STRAORDINARIO NOTTURNO</li>
                        <li><strong>CIL</strong> - CORSO INTRA-LAVORATIVO</li>
                    </ul>
                </td>
                <td style="width: 20%">
                    <ul style="list-style: none; padding-left: 0; margin: 0;">
                        <li><strong>STF</strong> - STRAORDINARIO FESTIVO</li>
                        <li><strong>STNF</strong> - STRAORDINARIO NOTTURNO/FESTIVO</li>
                        <li><strong>OV</strong> - ORE VIAGGIO</li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
