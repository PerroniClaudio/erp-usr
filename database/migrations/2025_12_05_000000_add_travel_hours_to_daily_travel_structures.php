<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('daily_travel_structures', 'travel_hours')) {
            // economic_value may have been removed by a previous migration, so fall back to an existing column
            $afterColumn = Schema::hasColumn('daily_travel_structures', 'economic_value')
                ? 'economic_value'
                : 'cost_per_km';

            Schema::table('daily_travel_structures', function (Blueprint $table) use ($afterColumn) {
                $table->decimal('travel_hours', 6, 2)->default(0)->after($afterColumn);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('daily_travel_structures', 'travel_hours')) {
            Schema::table('daily_travel_structures', function (Blueprint $table) {
                $table->dropColumn('travel_hours');
            });
        }
    }
};
