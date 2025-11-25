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
        Schema::table('daily_travel_structures', function (Blueprint $table) {
            $table->decimal('economic_value', 10, 2)->default(0)->after('cost_per_km');
            $table->integer('travel_minutes')->default(0)->after('economic_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_travel_structures', function (Blueprint $table) {
            $table->dropColumn(['economic_value', 'travel_minutes']);
        });
    }
};
