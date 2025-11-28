<?php

namespace App\Imports;

use App\Models\Vehicle;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;

class VehicleImport implements ToCollection, WithChunkReading {
    public function collection(Collection $rows) {
        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Salta l'intestazione

            if ($row[2] == null) {
                continue; // Salta le righe con price_per_km nullo
            }

            $pricePerKm = round((float) $row[2], 4);

            $fringe_benefit_25 = $pricePerKm * 0.25 * 15000;
            $fringe_benefit_30 = $pricePerKm * 0.30 * 15000;
            $fringe_benefit_50 = $pricePerKm * 0.50 * 15000;
            $fringe_benefit_60 = $pricePerKm * 0.60 * 15000;

            Vehicle::updateOrCreate(
                [
                    'brand' => $row[0],
                    'model' => $row[1],
                ],
                [
                    'price_per_km' => $pricePerKm,
                    'fringe_benefit_25' => $fringe_benefit_25,
                    'fringe_benefit_30' => $fringe_benefit_30,
                    'fringe_benefit_50' => $fringe_benefit_50,
                    'fringe_benefit_60' => $fringe_benefit_60,
                    'last_update' => now(),
                ]
            );
        }
    }

    public function chunkSize(): int {
        return 100; // Elabora 100 righe alla volta
    }
}
