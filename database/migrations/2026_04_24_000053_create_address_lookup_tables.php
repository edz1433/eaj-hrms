<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Philippine Standard Geographic Code (PSGC) address lookup tables.
 * Seed data: import the PSGC publication from PSA (psa.gov.ph).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();        // PSGC code
            $table->string('name');
            $table->string('region_id')->nullable();
            // no timestamps — static reference data
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('region_id')->nullable()->index();
            $table->string('province_id')->nullable();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('region_id')->nullable()->index();
            $table->string('province_id')->nullable()->index();
            $table->string('city_id')->nullable();
        });

        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('region_id')->nullable()->index();
            $table->string('province_id')->nullable()->index();
            $table->string('city_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangays');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('regions');
    }
};
