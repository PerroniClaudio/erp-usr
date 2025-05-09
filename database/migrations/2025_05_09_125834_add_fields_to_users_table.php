<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('title')->nullable()->after('name');
            $table->string('cfp')->nullable()->after('title');
            $table->date('birth_date')->nullable()->after('cfp');
            $table->string('mobile_number')->nullable()->after('birth_date');
            $table->string('phone_number')->nullable()->after('mobile_number');
            $table->string('category')->nullable()->after('phone_number');
            $table->string('weekly_hours')->nullable()->after('category');
            $table->string('badge_code')->nullable()->after('weekly_hours');
            $table->string('company_name')->nullable()->after('badge_code');
            $table->string('vat_number')->nullable()->after('company_name');
            $table->string('employee_code')->nullable()->after('vat_number');

            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('street_number')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('province')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();

            $table->string('location_address')->nullable();
            $table->string('location_city')->nullable();
            $table->string('location_street_number')->nullable();
            $table->string('location_postal_code')->nullable();
            $table->string('location_province')->nullable();
            $table->string('location_latitude')->nullable();
            $table->string('location_longitude')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('title');
            $table->dropColumn('cfp');
            $table->dropColumn('birth_date');
            $table->dropColumn('mobile_number');
            $table->dropColumn('phone_number');
            $table->dropColumn('category');
            $table->dropColumn('weekly_hours');
            $table->dropColumn('badge_code');
            $table->dropColumn('company_name');
            $table->dropColumn('vat_number');
            $table->dropColumn('employee_code');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('location_address');
            $table->dropColumn('location_city');
            $table->dropColumn('location_street_number');
            $table->dropColumn('location_postal_code');
            $table->dropColumn('location_province');
            $table->dropColumn('location_latitude');
            $table->dropColumn('location_longitude');
        });
    }
};
