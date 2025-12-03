<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_travel_steps', function (Blueprint $table) {
            $table->decimal('economic_value', 10, 2)->default(0)->after('time_difference');
        });

        DB::table('daily_travel_structures')
            ->select(['id', 'economic_value'])
            ->chunkById(200, function ($structures) {
                foreach ($structures as $structure) {
                    if ((float) $structure->economic_value <= 0) {
                        continue;
                    }

                    $firstStepId = DB::table('daily_travel_steps')
                        ->where('daily_travel_structure_id', $structure->id)
                        ->orderBy('step_number')
                        ->value('id');

                    if ($firstStepId) {
                        DB::table('daily_travel_steps')
                            ->where('id', $firstStepId)
                            ->update(['economic_value' => $structure->economic_value]);
                    }
                }
            });

        if (Schema::hasColumn('daily_travel_structures', 'economic_value')) {
            Schema::table('daily_travel_structures', function (Blueprint $table) {
                $table->dropColumn('economic_value');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('daily_travel_structures', 'economic_value')) {
            Schema::table('daily_travel_structures', function (Blueprint $table) {
                $table->decimal('economic_value', 10, 2)->default(0)->after('cost_per_km');
            });
        }

        DB::table('daily_travel_structures')
            ->select('id')
            ->chunkById(200, function ($structures) {
                foreach ($structures as $structure) {
                    $sum = DB::table('daily_travel_steps')
                        ->where('daily_travel_structure_id', $structure->id)
                        ->sum('economic_value');

                    DB::table('daily_travel_structures')
                        ->where('id', $structure->id)
                        ->update(['economic_value' => $sum]);
                }
            });

        Schema::table('daily_travel_steps', function (Blueprint $table) {
            $table->dropColumn('economic_value');
        });
    }
};
