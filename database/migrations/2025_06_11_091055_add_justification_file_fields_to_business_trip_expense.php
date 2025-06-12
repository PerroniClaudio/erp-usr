<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('business_trip_expenses', function (Blueprint $table) {
            //
            $table->string('justification_file_path')->nullable()->after('amount');
            $table->string('justification_file_name')->nullable()->after('justification_file_path');
            $table->string('justification_file_mime_type')->nullable()->after('justification_file_name');
            $table->string('justification_file_size')->nullable()->after('justification_file_mime_type');
            $table->datetime('justification_file_uploaded_at')->nullable()->after('justification_file_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('business_trip_expenses', function (Blueprint $table) {
            //
            $table->dropColumn([
                'justification_file_path',
                'justification_file_name',
                'justification_file_mime_type',
                'justification_file_size',
                'justification_file_uploaded_at'
            ]);
        });
    }
};
