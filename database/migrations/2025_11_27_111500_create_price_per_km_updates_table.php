<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('price_per_km_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price_per_km', 12, 4);
            $table->date('update_date');
            $table->timestamps();
        });

        if (Schema::hasTable('vehicles')) {
            $now = now();
            $vehicles = DB::table('vehicles')
                ->select('id', 'price_per_km', 'last_update')
                ->get();

            foreach ($vehicles as $vehicle) {
                DB::table('price_per_km_updates')->insert([
                    'vehicle_id' => $vehicle->id,
                    'user_id' => null,
                    'price_per_km' => $vehicle->price_per_km,
                    'update_date' => $vehicle->last_update
                        ? Carbon::parse($vehicle->last_update)->toDateString()
                        : $now->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('price_per_km_updates');
    }
};
