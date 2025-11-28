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
        Schema::table('file_objects', function (Blueprint $table) {
            $table->foreignId('protocol_id')->nullable()->after('file_object_sector_id')->constrained()->nullOnDelete();
            $table->string('protocol_number')->nullable()->after('protocol_id');
            $table->unsignedInteger('protocol_sequence')->nullable()->after('protocol_number');
            $table->unsignedInteger('protocol_year')->nullable()->after('protocol_sequence');
            $table->date('valid_at')->nullable()->after('expires_at');

            $table->unique('protocol_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_objects', function (Blueprint $table) {
            $table->dropUnique(['protocol_number']);
            $table->dropForeign(['protocol_id']);
            $table->dropColumn(['protocol_year', 'protocol_sequence', 'protocol_number', 'protocol_id', 'valid_at']);
        });
    }
};
