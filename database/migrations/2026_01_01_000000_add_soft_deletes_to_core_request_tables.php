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
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('time_off_requests', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('time_off_requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
