<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\TimeOffRequest;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class CedolinoExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    WithCustomStartCell,
    WithColumnWidths {
    protected $user;
    protected $mese;
    protected $anno;
    protected $giorni;

    public function __construct(User $user, $mese, $anno) {
        $this->user = $user;
        $this->mese = $mese;
        $this->anno = $anno;
        $this->giorni = $this->getGiorniMese($mese, $anno);
    }

    public function collection() {
        // Definiamo i tipi di attività
        $tipiAttivita = [
            'LA' => 'LAVORATO',
            'SW' => 'SMART WORKING',
            'FE' => 'FERIE',
            'MA' => 'MALATTIA',
            'ROL' => 'ROL',
            'ST' => 'STRAORDINARIO',
            'STN' => 'STRAORDINARIO NOTTURNO',
            'STF' => 'STRAORDINARIO FESTIVO',
            'STNF' => 'STRAORDINARIO NOTTURNO/FESTIVO',
            'LM' => 'LICENZA MATRIMONIO',
            'OV' => 'ORE VIAGGIO'
        ];

        // Otteniamo il primo e l'ultimo giorno del mese
        $mesiMap = [
            'Gennaio' => 1,
            'Febbraio' => 2,
            'Marzo' => 3,
            'Aprile' => 4,
            'Maggio' => 5,
            'Giugno' => 6,
            'Luglio' => 7,
            'Agosto' => 8,
            'Settembre' => 9,
            'Ottobre' => 10,
            'Novembre' => 11,
            'Dicembre' => 12
        ];

        $meseNumero = $mesiMap[$this->mese];
        $primoGiorno = Carbon::createFromDate($this->anno, $meseNumero, 1)->startOfDay();
        $ultimoGiorno = Carbon::createFromDate($this->anno, $meseNumero, 1)->endOfMonth()->endOfDay();

        // Otteniamo le presenze dell'utente per il mese specificato
        $attendances = Attendance::where('user_id', $this->user->id)
            ->whereBetween('date', [$primoGiorno, $ultimoGiorno])
            ->get();

        // Otteniamo le richieste di ferie/permessi dell'utente per il mese specificato
        $timeOffRequests = TimeOffRequest::where('user_id', $this->user->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($primoGiorno, $ultimoGiorno) {
                $query->whereBetween('start_date', [$primoGiorno, $ultimoGiorno])
                    ->orWhereBetween('end_date', [$primoGiorno, $ultimoGiorno]);
            })
            ->get();

        // Prepariamo i dati per ogni tipo di attività
        $datiGiorni = [];
        foreach ($tipiAttivita as $codice => $descrizione) {
            $datiGiorni[$codice] = array_fill(1, count($this->giorni), null);
        }

        // Popoliamo i dati delle presenze
        foreach ($attendances as $attendance) {
            $giorno = Carbon::parse($attendance->date)->day;

            // Determiniamo il tipo di attività in base ai dati di presenza
            if ($attendance->is_remote) {
                $datiGiorni['SW'][$giorno] = $attendance->hours_worked;
            } else {
                $datiGiorni['LA'][$giorno] = $attendance->hours_worked;
            }

            // Aggiungiamo gli straordinari se presenti
            if ($attendance->overtime_hours > 0) {
                if ($attendance->is_holiday) {
                    $datiGiorni['STF'][$giorno] = $attendance->overtime_hours;
                } elseif ($attendance->is_night_shift) {
                    $datiGiorni['STN'][$giorno] = $attendance->overtime_hours;
                } else {
                    $datiGiorni['ST'][$giorno] = $attendance->overtime_hours;
                }
            }

            // Aggiungiamo le ore di viaggio se presenti
            if (isset($attendance->travel_hours) && $attendance->travel_hours > 0) {
                $datiGiorni['OV'][$giorno] = $attendance->travel_hours;
            }
        }

        // Popoliamo i dati delle ferie/permessi
        foreach ($timeOffRequests as $request) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Iteriamo su tutti i giorni della richiesta
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                // Verifichiamo che il giorno sia nel mese corrente
                if ($date->month == $meseNumero && $date->year == $this->anno) {
                    $giorno = $date->day;

                    // Determiniamo il tipo di assenza
                    switch ($request->type) {
                        case 'vacation':
                            $datiGiorni['FE'][$giorno] = 8; // Assumiamo 8 ore per un giorno intero
                            break;
                        case 'sick_leave':
                            $datiGiorni['MA'][$giorno] = 8;
                            break;
                        case 'rol':
                            $datiGiorni['ROL'][$giorno] = 8;
                            break;
                        case 'marriage_leave':
                            $datiGiorni['LM'][$giorno] = 8;
                            break;
                            // Aggiungi altri tipi di assenza se necessario
                    }
                }
            }
        }

        // Creiamo le righe per l'export
        $rows = [];
        foreach ($tipiAttivita as $codice => $descrizione) {
            $row = [
                'tipo' => $codice,
                'nominativo' => $this->user->name,
            ];

            // Aggiungiamo i giorni
            $totOre = 0;
            $totGiorni = 0;

            for ($i = 1; $i <= count($this->giorni); $i++) {
                $ore = $datiGiorni[$codice][$i];
                $row['g' . $i] = $ore;

                if ($ore) {
                    $totOre += $ore;
                    $totGiorni += ($ore / 8);
                }
            }

            $row['tot_ore'] = $totOre;
            $row['tot_giorni'] = $totGiorni;

            $rows[] = $row;
        }

        return collect($rows);
    }

    public function headings(): array {
        $headings = ['TIPO', 'NOMINATIVO'];

        // Aggiungiamo le intestazioni dei giorni
        foreach ($this->giorni as $index => $giorno) {
            $dayNum = $index + 1;
            $dayLetter = substr($giorno['nome'], 0, 1);
            $headings[] = $dayLetter . "\n" . $dayNum;
        }

        $headings[] = 'TOT ORE';
        $headings[] = 'TOT GIORNI';

        return $headings;
    }

    public function map($row): array {
        $mappedRow = [$row['tipo'], $row['nominativo']];

        for ($i = 1; $i <= count($this->giorni); $i++) {
            $key = 'g' . $i;
            $mappedRow[] = $row[$key];
        }

        $mappedRow[] = $row['tot_ore'];
        $mappedRow[] = $row['tot_giorni'];

        return $mappedRow;
    }

    public function styles(Worksheet $sheet) {
        $lastColumn = chr(66 + count($this->giorni) + 1);

        // Stile intestazione
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->setCellValue('A1', 'IFORTECH S.R.L.');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:' . $lastColumn . '2');
        $sheet->setCellValue('A2', 'Via Ginestrino, 45 - Cologno Monzese - Milano - 20093');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:' . $lastColumn . '3');
        $sheet->setCellValue('A3', 'Cedolino: Mese ' . $this->mese);
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Stile legenda
        $sheet->mergeCells('A4:' . chr(65 + floor(count($this->giorni) / 2)) . '4');
        $sheet->setCellValue('A4', 'LAVORATO => LA
FERIE => FE
MALATTIA => MA
ROL => ROL
LICENZA MATRIMONIO => LM
SMART WORKING => SW');

        $sheet->mergeCells(chr(66 + floor(count($this->giorni) / 2)) . '4:' . $lastColumn . '4');
        $sheet->setCellValue(chr(66 + floor(count($this->giorni) / 2)) . '4', 'STRAORDINARIO => ST
STRAORDINARIO NOTTURNO => STN
STRAORDINARIO FESTIVO => STF
STRAORDINARIO NOTTURNO/FESTIVO => STNF
ORE VIAGGIO => OV');

        // Stile tabella principale
        $lastRow = 16; // 11 tipi di attività + intestazioni e spazi
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle('A6:' . $lastColumn . $lastRow)->applyFromArray($styleArray);
        $sheet->getStyle('A6:' . $lastColumn . '6')->getFont()->setBold(true);
        $sheet->getStyle('A6:' . $lastColumn . '6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDDDDD');

        // Allineamento centrato per tutte le celle della tabella
        $sheet->getStyle('A6:' . $lastColumn . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        return [
            6 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string {
        return 'Cedolino ' . $this->mese;
    }

    public function startCell(): string {
        return 'A6';
    }

    public function columnWidths(): array {
        $widths = [
            'A' => 10,
            'B' => 20,
        ];

        // Larghezza colonne giorni
        for ($i = 0; $i < count($this->giorni); $i++) {
            $widths[chr(67 + $i)] = 5;
        }

        // Larghezza colonne totali
        $widths[chr(67 + count($this->giorni))] = 10;
        $widths[chr(68 + count($this->giorni))] = 10;

        return $widths;
    }

    private function getGiorniMese($mese, $anno) {
        $mesi = [
            'Gennaio' => 1,
            'Febbraio' => 2,
            'Marzo' => 3,
            'Aprile' => 4,
            'Maggio' => 5,
            'Giugno' => 6,
            'Luglio' => 7,
            'Agosto' => 8,
            'Settembre' => 9,
            'Ottobre' => 10,
            'Novembre' => 11,
            'Dicembre' => 12
        ];

        $numMese = $mesi[$mese];
        $numGiorni = cal_days_in_month(CAL_GREGORIAN, $numMese, $anno);

        $giorni = [];
        for ($i = 1; $i <= $numGiorni; $i++) {
            $data = \DateTime::createFromFormat('Y-m-d', "$anno-$numMese-$i");
            $giorni[] = [
                'numero' => $i,
                'nome' => $data->format('D'),
                'giorno_settimana' => $data->format('N')
            ];
        }

        return $giorni;
    }
}
