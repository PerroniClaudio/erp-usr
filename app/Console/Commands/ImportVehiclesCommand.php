<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ImportVehiclesData;

class ImportVehiclesCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicles:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa i dati dei veicoli eseguendo il job ImportVehiclesData';

    /**
     * Execute the console command.
     */
    public function handle(): void {
        // Dispatch the job
        ImportVehiclesData::dispatch();

        $this->info('Il job ImportVehiclesData è stato avviato con successo.');
    }
}
