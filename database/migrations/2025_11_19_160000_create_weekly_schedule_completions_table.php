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
        Schema::create('weekly_schedule_completions', function (Blueprint $table) {
            $table->id();
            $table->date('week_start')->unique();
            $table->date('week_end');
            $table->unsignedInteger('total_users')->default(0);
            $table->unsignedInteger('completed_users')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_schedule_completions');
    }
};
