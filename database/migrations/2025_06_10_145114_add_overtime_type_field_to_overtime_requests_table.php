<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('overtime_requests', function (Blueprint $table) {
            //
            $table->foreignId('overtime_type_id')
                ->default(1)
                ->constrained('overtime_types')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('overtime_requests', function (Blueprint $table) {
            //
            $table->dropForeign(['overtime_type_id']);
        });
    }
};
