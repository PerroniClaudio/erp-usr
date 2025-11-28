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
        Schema::create('protocols', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('acronym', 10);
            $table->unsignedInteger('counter')->default(1);
            $table->unsignedInteger('counter_year')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['acronym', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocols');
    }
};
