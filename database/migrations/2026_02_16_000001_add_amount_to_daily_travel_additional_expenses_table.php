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
        if (! Schema::hasColumn('daily_travel_additional_expenses', 'amount')) {
            Schema::table('daily_travel_additional_expenses', function (Blueprint $table) {
                $table->decimal('amount', 10, 2)->default(0)->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('daily_travel_additional_expenses', 'amount')) {
            Schema::table('daily_travel_additional_expenses', function (Blueprint $table) {
                $table->dropColumn('amount');
            });
        }
    }
};
