<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\AttendanceType;
use App\Models\Company;
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

    protected $cachedCompanies = [];
    protected $companyCache = [];

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

        Log::info("Starting legacy DB import for PEID: {$this->peid}, User ID: {$this->userId}, Start Date: {$this->startDate}, End Date: {$this->endDate}");

        $data = DB::connection('legacy_mysql')->table('presenze')
            ->join('combo_personale_tipologia', 'presenze.cptid', '=', 'combo_personale_tipologia.cptid')
            ->where('peid', $this->peid)
            ->whereBetween(DB::raw("STR_TO_DATE(data_inizio, '%d-%m-%Y')"), [$this->startDate, $this->endDate])
            ->orderBy(DB::raw("STR_TO_DATE(data_inizio, '%d-%m-%Y')"), 'ASC')
            ->select('voce', 'presenze.data_inizio', 'presenze.ora_inizio', 'presenze.ora_fine', 'presenze.aid')
            ->get();

        $ferie = [];

        Log::info('Total records fetched from legacy DB', ['count' => $data->count()]);

        foreach ($data as $row) {

            Log::info('Processing attendance record', [
                'aid' => $row->aid,
                'voce' => $row->voce,
                'data_inizio' => $row->data_inizio,
                'ora_inizio' => $row->ora_inizio,
                'ora_fine' => $row->ora_fine,
            ]);

            if (!in_array($row->aid, $this->cachedCompanies)) {
                $this->cachedCompanies[] = $row->aid;


                $azienda = DB::connection('legacy_mysql')->table('aziende')
                    ->where('aid', $row->aid ?? null)
                    ->first();

                if (!$azienda) {
                    Log::error("Azienda with aid " . ($row->aid ?? 'N/A') . " not found.");
                    continue;
                }

                if ($azienda->name == 'IFORTECH S.R.L.') {
                    $company = Company::where('id', 1)->first();
                } else {
                    $company = Company::where('name', $azienda->name)->first();
                }

                if (!$company) {
                    $company = Company::create([
                        'name' => $azienda->name
                    ]);
                }

                $this->companyCache[$row->aid] = $company;
                $company_id = $company->id;
            } else {
                $company_id = $this->companyCache[$row->aid]->id;
            }



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
                    'company_id' => $company_id,
                    'time_off_type_id' => $timeOffType->id,
                    'status' => 2,
                    'date_from' => \Carbon\Carbon::createFromFormat('d-m-Y H:i', $date_from)->format('Y-m-d H:i'),
                    'date_to' => \Carbon\Carbon::createFromFormat('d-m-Y H:i', $date_to)->format('Y-m-d H:i'),
                ];
            } else {

                // Presenza
                // Log attendance data before creating
                Log::info('Creating attendance record', [
                    'user_id' => $this->userId,
                    'company_id' => $company_id,
                    'date' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data_inizio)->format('Y-m-d'),
                    'time_in' => $row->ora_inizio,
                    'time_out' => $row->ora_fine,
                    'hours' => (strtotime($row->ora_fine) - strtotime($row->ora_inizio)) / 3600,
                    'attendance_type_id' => $attendance_type->id,
                ]);

                Attendance::create([
                    'user_id' => $this->userId,
                    'company_id' => $company_id,
                    'date' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data_inizio)->format('Y-m-d'),
                    'time_in' => $row->ora_inizio,
                    'time_out' => $row->ora_fine,
                    'hours' => (strtotime($row->ora_fine) - strtotime($row->ora_inizio)) / 3600,
                    'attendance_type_id' => $attendance_type->id,
                ]);
            }
        }

        return;

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
