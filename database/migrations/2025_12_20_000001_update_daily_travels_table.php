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
        Schema::table('daily_travels', function (Blueprint $table) {
            if (Schema::hasColumn('daily_travels', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }

            if (!Schema::hasColumn('daily_travels', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('travel_date');
            }

            if (!Schema::hasColumn('daily_travels', 'approved_by')) {
                $table->foreignId('approved_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_travels', function (Blueprint $table) {
            if (Schema::hasColumn('daily_travels', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }

            if (Schema::hasColumn('daily_travels', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (!Schema::hasColumn('daily_travels', 'company_id')) {
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            }
        });
    }
};
