<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('failed_attendances', function (Blueprint $table) {
            $table->integer('request_type')->default(0)->after('requested_hours'); // 0 = permesso, 1 = presenza
            $table->time('requested_time_in_morning')->nullable()->after('request_type');
            $table->time('requested_time_out_morning')->nullable()->after('requested_time_in_morning');
            $table->time('requested_time_in_afternoon')->nullable()->after('requested_time_out_morning');
            $table->time('requested_time_out_afternoon')->nullable()->after('requested_time_in_afternoon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('failed_attendances', function (Blueprint $table) {
            $table->dropColumn(['request_type', 'requested_time_in_morning', 'requested_time_out_morning', 'requested_time_in_afternoon', 'requested_time_out_afternoon']);
        });
    }
};
