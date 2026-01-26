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
        Schema::table('time_off_amounts', function (Blueprint $table) {
            $table->decimal('time_off_amount', 13, 5)->change();
            $table->decimal('rol_amount', 13, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_off_amounts', function (Blueprint $table) {
            $table->decimal('time_off_amount', 8, 2)->change();
            $table->decimal('rol_amount', 8, 2)->change();
        });
    }
};
