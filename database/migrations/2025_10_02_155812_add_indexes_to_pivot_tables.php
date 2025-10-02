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
        // Indici per user_companies
        Schema::table('user_companies', function (Blueprint $table) {
            // Indice composito unico per evitare duplicati
            $table->unique(['user_id', 'company_id']);
        });

        // Indici per groups_users
        Schema::table('groups_users', function (Blueprint $table) {
            // Indice composito unico per evitare duplicati
            $table->unique(['group_id', 'user_id']);
        });

        // Indici per user_vehicle
        Schema::table('user_vehicle', function (Blueprint $table) {
            // Indice composito unico per evitare duplicati
            $table->unique(['user_id', 'vehicle_id']);

            // Indici aggiuntivi per query comuni
            $table->index('plate_number');
            $table->index('vehicle_type');
            $table->index('ownership_type');
            $table->index('contract_start_date');
            $table->index('contract_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_companies', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'company_id']);
        });

        Schema::table('groups_users', function (Blueprint $table) {
            $table->dropUnique(['group_id', 'user_id']);
        });

        Schema::table('user_vehicle', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'vehicle_id']);
            $table->dropIndex(['user_vehicle_plate_number_index']);
            $table->dropIndex(['user_vehicle_vehicle_type_index']);
            $table->dropIndex(['user_vehicle_ownership_type_index']);
            $table->dropIndex(['user_vehicle_contract_start_date_index']);
            $table->dropIndex(['user_vehicle_contract_end_date_index']);
        });
    }
};
