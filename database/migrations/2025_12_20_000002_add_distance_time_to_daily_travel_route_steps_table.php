<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_travel_route_steps', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_travel_route_steps', 'distance_km')) {
                $table->decimal('distance_km', 8, 2)->nullable()->after('longitude');
            }

            if (!Schema::hasColumn('daily_travel_route_steps', 'travel_minutes')) {
                $table->unsignedInteger('travel_minutes')->nullable()->after('distance_km');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_travel_route_steps', function (Blueprint $table) {
            if (Schema::hasColumn('daily_travel_route_steps', 'travel_minutes')) {
                $table->dropColumn('travel_minutes');
            }

            if (Schema::hasColumn('daily_travel_route_steps', 'distance_km')) {
                $table->dropColumn('distance_km');
            }
        });
    }
};
