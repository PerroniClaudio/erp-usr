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
        Schema::create('daily_travel_steps', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('daily_travel_structure_id')->constrained()->cascadeOnDelete();
            $table->integer('step_number');
            $table->string('address');
            $table->string('city');
            $table->string('province');
            $table->string('zip_code');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_travel_steps');
    }
};
