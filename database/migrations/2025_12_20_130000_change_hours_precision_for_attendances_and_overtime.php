<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Update hours columns to keep two decimals instead of rounding to integers.
     */
    public function up(): void {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('hours', 8, 2)->change();
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->decimal('hours', 8, 2)->change();
        });
    }

    /**
     * Revert hours columns back to integers.
     */
    public function down(): void {
        Schema::table('attendances', function (Blueprint $table) {
            $table->integer('hours')->change();
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->integer('hours')->change();
        });
    }
};
