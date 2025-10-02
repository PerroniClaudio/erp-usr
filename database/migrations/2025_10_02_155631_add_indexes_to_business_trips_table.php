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
        Schema::table('business_trips', function (Blueprint $table) {
            // Indici per le date di viaggio
            $table->index('date_from');
            $table->index('date_to');

            // Indice per il status
            $table->index('status');

            // Indice per il tipo di spesa
            $table->index('expense_type');

            // Indici compositi per query comuni
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'date_from', 'date_to']);
            $table->index(['status', 'date_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_trips', function (Blueprint $table) {
            $table->dropIndex(['business_trips_date_from_index']);
            $table->dropIndex(['business_trips_date_to_index']);
            $table->dropIndex(['business_trips_status_index']);
            $table->dropIndex(['business_trips_expense_type_index']);
            $table->dropIndex(['business_trips_user_id_status_index']);
            $table->dropIndex(['business_trips_user_id_date_from_date_to_index']);
            $table->dropIndex(['business_trips_status_date_from_index']);
        });
    }
};
