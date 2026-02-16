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
        Schema::create('daily_travel_additional_expenses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('daily_travel_id')->constrained('daily_travels')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->dateTime('occurred_at');
            $table->string('proof_file_path');
            $table->string('proof_file_name');
            $table->string('proof_file_mime_type')->nullable();
            $table->unsignedBigInteger('proof_file_size')->nullable();

            $table->index(['daily_travel_id', 'occurred_at'], 'daily_travel_additional_expenses_travel_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_travel_additional_expenses');
    }
};
