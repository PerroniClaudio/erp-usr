<?php

namespace App\Jobs;

use App\Imports\VehicleImport;
use App\Models\Vehicle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ImportVehiclesData implements ShouldQueue {
    use Queueable;

    private $files = [
        "Autocaravan.xlsx",
        "Benzina-IN.xlsx",
        "Elettrico-IN.xlsx",
        "Gasolio-IN.xlsx",
        "GPL-IN.xlsx",
        "Ibr-Benzina-IN.xlsx",
        "Ibr-Gasolio-IN.xlsx",
        "Plug-in-IN.xlsx",
        "Benzina-OUT.xlsx",
        "Elettrico-OUT.xlsx",
        "Gasolio-OUT.xlsx",
        "GPL-Metano-OUT.xlsx",
        "Ibr-Benzina-OUT.xlsx",
        "Ibr-Gasolio-OUT.xlsx",
        "Plug-in-OUT.xlsx",
        "Motoveicoli.xlsx",
    ];

    public function __construct() {
        //
    }

    public function handle(): void {
        $currentYear = date('Y');
        $url_prefix = "https://aci.gov.it/app/uploads/2024/12/";

        foreach ($this->files as $file) {
            $url = ($file == "Gasolio-IN.xlsx")
                ? "https://aci.gov.it/app/uploads/2025/01/Gasolio-IN.xlsx"
                : $url_prefix . $file;

            // Scarica il file solo se non esiste
            if (!Storage::exists($file)) {
                Log::info('Downloading file', ['file' => $file, 'url' => $url]);
                $fileContents = file_get_contents($url);
                Storage::put($file, $fileContents);
            }

            // Elabora il file con chunking
            $filePath = storage_path('app/private/' . $file);
            Excel::import(new VehicleImport, $filePath);

            // Elimina il file dopo l'elaborazione
            if (Storage::exists($file)) {
                Storage::delete($file);
            }
        }
    }
}
