<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('overtime_type_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('date');
            $table->time('time_in');
            $table->time('time_out');
            $table->integer('hours');
            $table->integer('status')->default(0); // 0: creata, 1: in attesa, 2: approvata, 3: rifiutata
            $table->string('batch_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('overtime_requests');
    }
};
