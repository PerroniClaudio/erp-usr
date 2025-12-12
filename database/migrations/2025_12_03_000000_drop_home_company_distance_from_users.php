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
        if (Schema::hasColumn('users', 'home_company_distance_km')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('home_company_distance_km');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('users', 'home_company_distance_km')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('home_company_distance_km', 8, 2)->default(0)->after('color');
            });
        }
    }
};
