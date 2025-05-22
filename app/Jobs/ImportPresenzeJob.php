<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\AttendanceType;
use App\Models\TimeOffRequest;
use App\Models\TimeOffType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportPresenzeJob implements ShouldQueue {
    use Queueable;

    protected $peid;
    protected $userId;
    protected $startDate;
    protected $endDate;

    /**
     * Create a new job instance.
     */
    public function __construct(int $peid, int $userId, string $startDate, string $endDate) {
        $this->peid = $peid;
        $this->userId = $userId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        $data = DB::connection('legacy_mysql')->table('presenze')
            ->join('combo_personale_tipologia', 'presenze.cptid', '=', 'combo_personale_tipologia.cptid')
            ->where('peid', $this->peid)
            ->whereBetween(DB::raw("STR_TO_DATE(data_inizio, '%d-%m-%Y')"), [$this->startDate, $this->endDate])
            ->orderBy(DB::raw("STR_TO_DATE(data_inizio, '%d-%m-%Y')"), 'ASC')
            ->select('voce', 'presenze.data_inizio', 'presenze.ora_inizio', 'presenze.ora_fine')
            ->get();

        $ferie = [];

        foreach ($data as $row) {
            $attendance_type = AttendanceType::where('name', $row->voce)->first();

            if (!$attendance_type) {
                // Giorno di ferie o rol
                $timeOffType = TimeOffType::where('name', $row->voce)->first();

                if (!$timeOffType) {
                    return;
                }


                $date_from = $row->data_inizio . ' ' . $row->ora_inizio;
                $date_to = $row->data_inizio . ' ' . $row->ora_fine;

                try {
                    $fromCarbon = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $date_from);
                    $toCarbon = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $date_to);
                    $fromErrors = \Carbon\Carbon::getLastErrors();
                    $toErrors = \Carbon\Carbon::getLastErrors();
                    if (
                        $fromErrors['error_count'] > 0 || $fromErrors['warning_count'] > 0 ||
                        $toErrors['error_count'] > 0 || $toErrors['warning_count'] > 0
                    ) {
                        continue; // Skip if date format is invalid
                    }
                } catch (\Exception $e) {
                    continue; // Skip if date format is invalid
                }



                $ferie[] = [
                    'user_id' => $this->userId,
                    'company_id' => 1,
                    'time_off_type_id' => $timeOffType->id,
                    'status' => 2,
                    'date_from' => \Carbon\Carbon::createFromFormat('d-m-Y H:i', $date_from)->format('Y-m-d H:i'),
                    'date_to' => \Carbon\Carbon::createFromFormat('d-m-Y H:i', $date_to)->format('Y-m-d H:i'),
                ];
            } else {
                // Presenza
                Attendance::create([
                    'user_id' => $this->userId,
                    'company_id' => 1,
                    'date' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data_inizio)->format('Y-m-d'),
                    'time_in' => $row->ora_inizio,
                    'time_out' => $row->ora_fine,
                    'hours' => (strtotime($row->ora_fine) - strtotime($row->ora_inizio)) / 3600,
                    'attendance_type_id' => $attendance_type->id,
                ]);
            }
        }

        $groupedFerie = [];

        foreach ($ferie as $ferieItem) {
            $dateFrom = \Carbon\Carbon::parse($ferieItem['date_from'])->format('Y-m-d');
            if (!isset($groupedFerie[$dateFrom])) {
                $groupedFerie[$dateFrom] = [];
            }
            $groupedFerie[$dateFrom][] = $ferieItem;
        }

        $formattedFerie = [];

        foreach ($groupedFerie as $date => $items) {
            $batchId = uniqid();
            foreach ($items as $item) {
                $item['batch_id'] = $batchId;

                $formattedFerie[] = $item;
            }
        }

        foreach ($formattedFerie as $ferieItem) {
            TimeOffRequest::create($ferieItem);
        }
    }
}
