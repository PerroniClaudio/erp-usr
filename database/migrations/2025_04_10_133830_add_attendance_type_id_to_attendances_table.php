<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('attendances', function (Blueprint $table) {
            //

            // Add the attendance_type_id column to the attendances table
            $table->foreignId('attendance_type_id')->nullable()->constrained('attendance_types')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('attendances', function (Blueprint $table) {
            //
            // Drop the attendance_type_id column from the attendances table
            $table->dropForeign(['attendance_type_id']);
            $table->dropColumn('attendance_type_id');
        });
    }
};
