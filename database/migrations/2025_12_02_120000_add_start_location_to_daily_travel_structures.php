<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_travel_structures', function (Blueprint $table) {
            $table->string('start_location', 20)->default('office')->after('company_id');
        });

        DB::table('daily_travel_structures')->update(['start_location' => 'office']);

        Schema::table('daily_travel_structures', function (Blueprint $table) {
            $table->unique(['user_id', 'company_id', 'start_location'], 'daily_travel_structures_unique_trip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_travel_structures', function (Blueprint $table) {
            $table->dropUnique('daily_travel_structures_unique_trip');
            $table->dropColumn('start_location');
        });
    }
};
