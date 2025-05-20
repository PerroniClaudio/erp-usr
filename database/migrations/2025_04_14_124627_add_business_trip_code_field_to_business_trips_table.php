<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('business_trips', function (Blueprint $table) {
            //
            // Adding a new column 'code' to the 'business_trip' table
            $table->string('code')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('business_trips', function (Blueprint $table) {
            //
            // Dropping the 'code' column from the 'business_trip' table
            $table->dropColumn('code');
        });
    }
};
