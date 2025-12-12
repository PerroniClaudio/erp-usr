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
            Schema::table('daily_travel_structures', function (Blueprint $table) {
                $table->decimal('travel_hours', 6, 2)->default(0)->after('economic_value');
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
