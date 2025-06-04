<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('failed_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->integer('status')->default(0); // 0 = mostra messaggio all'utente, 1 = email di richiesta inviata, 2 = giustificata, 3 = rifiutata
            $table->text('reason')->nullable();
            $table->integer('requested_type')->default(0); // 0 = rol, 1 = ferie
            $table->integer('requested_hours')->default(0); // Ore richieste per la giustificazione
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('failed_attendances');
    }
};
