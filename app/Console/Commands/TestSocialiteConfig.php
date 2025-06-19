<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class TestSocialiteConfig extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socialite:test-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Socialite configuration and session setup';

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->info('Testing Socialite Configuration...');
        $this->newLine();

        // Test Microsoft OAuth config
        $this->testMicrosoftConfig();

        // Test session configuration
        $this->testSessionConfig();

        // Test database connection (for sessions)
        $this->testDatabaseConnection();

        // Test cache connection
        $this->testCacheConnection();

        $this->newLine();
        $this->info('Configuration test completed!');
    }

    private function testMicrosoftConfig() {
        $this->info('1. Testing Microsoft OAuth Configuration:');

        $clientId = config('services.microsoft.client_id');
        $clientSecret = config('services.microsoft.client_secret');
        $redirectUri = config('services.microsoft.redirect');
        $tenant = config('services.microsoft.tenant');

        if ($clientId && $clientSecret && $redirectUri) {
            $this->info('   ✓ Microsoft OAuth credentials configured');
            $this->line("   - Client ID: " . substr($clientId, 0, 8) . "...");
            $this->line("   - Redirect URI: {$redirectUri}");
            $this->line("   - Tenant: " . ($tenant ?: 'common'));
        } else {
            $this->error('   ✗ Microsoft OAuth credentials missing or incomplete');
        }
    }

    private function testSessionConfig() {
        $this->info('2. Testing Session Configuration:');

        $driver = config('session.driver');
        $lifetime = config('session.lifetime');
        $secure = config('session.secure');
        $httpOnly = config('session.http_only');
        $sameSite = config('session.same_site');

        $this->line("   - Driver: {$driver}");
        $this->line("   - Lifetime: {$lifetime} minutes");
        $this->line("   - Secure: " . ($secure ? 'true' : 'false'));
        $this->line("   - HTTP Only: " . ($httpOnly ? 'true' : 'false'));
        $this->line("   - Same Site: {$sameSite}");

        if ($driver === 'database') {
            try {
                $table = config('session.table', 'sessions');
                $exists = DB::getSchemaBuilder()->hasTable($table);
                if ($exists) {
                    $this->info("   ✓ Session table '{$table}' exists");
                } else {
                    $this->error("   ✗ Session table '{$table}' does not exist");
                    $this->line("     Run: php artisan session:table && php artisan migrate");
                }
            } catch (\Exception $e) {
                $this->error("   ✗ Error checking session table: " . $e->getMessage());
            }
        }
    }

    private function testDatabaseConnection() {
        $this->info('3. Testing Database Connection:');

        try {
            DB::connection()->getPdo();
            $this->info('   ✓ Database connection successful');
        } catch (\Exception $e) {
            $this->error('   ✗ Database connection failed: ' . $e->getMessage());
        }
    }

    private function testCacheConnection() {
        $this->info('4. Testing Cache Connection:');

        try {
            $driver = config('cache.default');
            $this->line("   - Cache Driver: {$driver}");

            Cache::put('socialite_test', 'working', 60);
            $value = Cache::get('socialite_test');

            if ($value === 'working') {
                $this->info('   ✓ Cache connection successful');
                Cache::forget('socialite_test');
            } else {
                $this->error('   ✗ Cache not working properly');
            }
        } catch (\Exception $e) {
            $this->error('   ✗ Cache connection failed: ' . $e->getMessage());
        }
    }
}
