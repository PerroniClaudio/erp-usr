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
        Schema::table('attendances', function (Blueprint $table) {
            // Indici singoli per migliorare le performance delle query
            $table->index('date');
            $table->index('user_id');
            $table->index('company_id');

            // Indice composito per query che filtrano per utente e data
            $table->index(['user_id', 'date']);

            // Indice composito per query che filtrano per azienda e data
            $table->index(['company_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['attendances_date_index']);
            $table->dropIndex(['attendances_user_id_index']);
            $table->dropIndex(['attendances_company_id_index']);
            $table->dropIndex(['attendances_user_id_date_index']);
            $table->dropIndex(['attendances_company_id_date_index']);
        });
    }
};
