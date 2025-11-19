<?php

use App\Models\AttendanceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_default_schedules', function (Blueprint $table) {
            $table->foreignId('attendance_type_id')
                ->nullable()
                ->after('total_hours')
                ->constrained('attendance_types')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('user_schedules', function (Blueprint $table) {
            $table->foreignId('attendance_type_id')
                ->nullable()
                ->after('total_hours')
                ->constrained('attendance_types')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        $defaultTypeId = AttendanceType::where('acronym', 'LS')->value('id')
            ?? AttendanceType::query()->value('id');
        $overtimeTypeId = AttendanceType::where('name', 'Straordinario')->value('id')
            ?? $defaultTypeId;

        if (! $defaultTypeId) {
            // No attendance types found, stop early.
            return;
        }

        DB::table('user_default_schedules')->update([
            'attendance_type_id' => DB::raw("CASE
                WHEN type = 'overtime' THEN " . ($overtimeTypeId ?? $defaultTypeId) . '
                ELSE ' . $defaultTypeId . '
            END'),
        ]);

        DB::table('user_schedules')->update([
            'attendance_type_id' => DB::raw("CASE
                WHEN type = 'overtime' THEN " . ($overtimeTypeId ?? $defaultTypeId) . '
                ELSE ' . $defaultTypeId . '
            END'),
        ]);

        Schema::table('user_default_schedules', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('user_schedules', function (Blueprint $table) {
            $table->dropColumn('type');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_default_schedules', function (Blueprint $table) {
            $table->enum('type', ['work', 'overtime'])->default('work');
        });

        Schema::table('user_schedules', function (Blueprint $table) {
            $table->enum('type', ['work', 'overtime'])->default('work');
        });

        $overtimeTypeId = AttendanceType::where('name', 'Straordinario')->value('id');

        DB::table('user_default_schedules')->update([
            'type' => DB::raw('CASE WHEN attendance_type_id = ' . ($overtimeTypeId ?? 0) . " THEN 'overtime' ELSE 'work' END"),
        ]);

        DB::table('user_schedules')->update([
            'type' => DB::raw('CASE WHEN attendance_type_id = ' . ($overtimeTypeId ?? 0) . " THEN 'overtime' ELSE 'work' END"),
        ]);

        Schema::table('user_default_schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('attendance_type_id');
        });

        Schema::table('user_schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('attendance_type_id');
        });
    }
};
