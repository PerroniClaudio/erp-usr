<?php

namespace App\Jobs;

use App\Models\BusinessTrip;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportTrasferte implements ShouldQueue {
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

        $user_vehicle = User::find($this->userId)->vehicles->first();

        $data = DB::connection('legacy_mysql')->table('trasferte_usr')
            ->join('trasferte_usr_itinerari', 'trasferte_usr.trpid', '=', 'trasferte_usr_itinerari.trpid')
            ->where('trasferte_usr.peid', $this->peid)
            ->whereBetween(DB::raw("STR_TO_DATE(trasferte_usr.data_inizio, '%d-%m-%Y')"), [$this->startDate, $this->endDate])
            ->orderBy(DB::raw("STR_TO_DATE(trasferte_usr.data_inizio, '%d-%m-%Y')"), 'ASC')
            ->select('trasferte_usr.*', 'trasferte_usr_itinerari.*')
            ->get();

        $trasferte = [];

        foreach ($data as $row) {
            // Raggruppa gli itinerari per nome
            if (!isset($trasferte[$row->nome])) {

                $coordinate_da = $row->coordda ? explode(',', $row->coordda) : null;

                if ($coordinate_da === null) {
                    Log::error("Coordinate not found for transfer: " . $row->nome . " on date: " . $row->data);
                    continue;
                }

                $trasferte[$row->nome] = [
                    'user_id' => $this->userId,
                    'date_from' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data_inizio)->format('Y-m-d'),
                    'date_to' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data_fine)->format('Y-m-d'),
                    'status' => $row->incorso,
                    'code' => $row->nome,
                    'trasferimenti' => [
                        [
                            'date' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data)->format('Y-m-d H:i:s'),
                            'address' => 'N/A',
                            'province' => 'N/A',
                            'city' => $row->da,
                            'zip_code' => 'N/A',
                            'latitude' => $coordinate_da ? $coordinate_da[0] : null,
                            'longitude' => $coordinate_da ? $coordinate_da[1] : null,
                        ]
                    ],
                ];
            }

            $coordinate = $row->coorda ? explode(',', $row->coorda) : null;

            if ($coordinate === null) {
                Log::error("Coordinate not found for transfer: " . $row->nome . " on date: " . $row->data);
                continue;
            }

            $trasferte[$row->nome]['trasferimenti'][] = [
                'date' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->data)->format('Y-m-d H:i:s'),
                'address' => 'N/A',
                'province' => 'N/A',
                'city' => $row->a,
                'zip_code' => 'N/A',
                'latitude' => $coordinate ? $coordinate[0] : null,
                'longitude' => $coordinate ? $coordinate[1] : null,
            ];
        }

        foreach ($trasferte as $code => $trasferta) {

            $businessTrip = BusinessTrip::create([
                'company_id' => 1,
                'user_id' => $trasferta['user_id'],
                'date_from' => $trasferta['date_from'],
                'date_to' => $trasferta['date_to'],
                'status' => $trasferta['status'],
                'expense_type' => 1,
                'code' => $code,
            ]);

            foreach ($trasferta['trasferimenti'] as $transfer) {
                $businessTrip->transfers()->create([
                    'business_trip_id' => $businessTrip->id,
                    'company_id' => 1,
                    'date' => $transfer['date'],
                    'address' => $transfer['address'],
                    'city' => $transfer['city'],
                    'province' => $transfer['province'],
                    'zip_code' => $transfer['zip_code'],
                    'latitude' => $transfer['latitude'],
                    'longitude' => $transfer['longitude'],
                    'vehicle_id' => $user_vehicle ? $user_vehicle->id : null,
                ]);
            }
        }
    }
}
