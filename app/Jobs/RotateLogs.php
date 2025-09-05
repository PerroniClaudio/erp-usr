<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RotateLogs implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //

        $now = Carbon::now();

        $logFiles = array_filter(
            glob(storage_path('logs/*')),
            function ($file) {
                return basename($file) !== '.gitignore';
            }
        );

        foreach ($logFiles as $file) {
            if (is_file($file) && filesize($file) > 0) {
                $file_name = $now->format('Y_m_d').'_'.basename($file);
                $file_path = 'logs/'.$file_name;
                $file->store($file_path, 's3');
                // Clear the log file after storing
                file_put_contents($file, '');
            }
        }
    }
}
