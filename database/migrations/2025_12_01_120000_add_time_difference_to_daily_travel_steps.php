<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('daily_travel_steps', function (Blueprint $table) {
            $table->integer('time_difference')->default(0)->after('step_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('daily_travel_steps', function (Blueprint $table) {
            $table->dropColumn('time_difference');
        });
    }
};
