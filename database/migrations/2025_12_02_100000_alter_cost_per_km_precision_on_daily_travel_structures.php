<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('daily_travel_structures', function (Blueprint $table) {
            $table->decimal('cost_per_km', 12, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('daily_travel_structures', function (Blueprint $table) {
            $table->decimal('cost_per_km', 10, 2)->change();
        });
    }
};
