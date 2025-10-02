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
        Schema::table('failed_attendances', function (Blueprint $table) {
            // Indici singoli per query comuni
            $table->index('date');
            $table->index('status');
            $table->index('requested_type');

            // Indici compositi per query che filtrano per utente
            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('failed_attendances', function (Blueprint $table) {
            $table->dropIndex(['failed_attendances_date_index']);
            $table->dropIndex(['failed_attendances_status_index']);
            $table->dropIndex(['failed_attendances_requested_type_index']);
            $table->dropIndex(['failed_attendances_user_id_date_index']);
            $table->dropIndex(['failed_attendances_user_id_status_index']);
            $table->dropIndex(['failed_attendances_status_date_index']);
        });
    }
};
