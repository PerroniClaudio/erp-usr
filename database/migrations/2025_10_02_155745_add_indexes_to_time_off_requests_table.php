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
        Schema::table('time_off_requests', function (Blueprint $table) {
            // Indici per le date delle richieste
            $table->index('date_from');
            $table->index('date_to');

            // Indice per lo status
            $table->index('status');

            // Indice per il batch_id
            $table->index('batch_id');

            // Indici compositi per query comuni
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'date_from', 'date_to']);
            $table->index(['company_id', 'status']);
            $table->index(['status', 'date_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_off_requests', function (Blueprint $table) {
            $table->dropIndex(['time_off_requests_date_from_index']);
            $table->dropIndex(['time_off_requests_date_to_index']);
            $table->dropIndex(['time_off_requests_status_index']);
            $table->dropIndex(['time_off_requests_batch_id_index']);
            $table->dropIndex(['time_off_requests_user_id_status_index']);
            $table->dropIndex(['time_off_requests_user_id_date_from_date_to_index']);
            $table->dropIndex(['time_off_requests_company_id_status_index']);
            $table->dropIndex(['time_off_requests_status_date_from_index']);
        });
    }
};
