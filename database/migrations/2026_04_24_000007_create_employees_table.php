<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // ── Identity ──────────────────────────────────────────────────────
            $table->string('prefix', 20)->nullable();
            $table->string('title_prefix', 30)->nullable();
            $table->string('fname');
            $table->string('mname')->nullable();
            $table->string('lname');
            $table->string('suffix', 20)->nullable();
            $table->string('emp_ID')->unique()->index();   // employee number
            $table->string('position')->nullable();
            $table->string('item_no')->nullable();

            // ── Organisational ────────────────────────────────────────────────
            $table->unsignedBigInteger('area_id')->nullable();   // logzone / area
            $table->unsignedBigInteger('camp_id')->nullable();   // campus
            $table->unsignedBigInteger('emp_dept')->nullable();  // department / office id
            $table->tinyInteger('emp_status')->default(1);       // 1=permanent,2=casual,3=contract,4=part-time
            $table->string('supervisor')->nullable();
            $table->date('date_hired')->nullable();
            $table->string('strat_function')->nullable();

            // ── Personal ──────────────────────────────────────────────────────
            $table->date('bdate')->nullable();
            $table->tinyInteger('age')->nullable();
            $table->string('b_place')->nullable();
            $table->string('sex', 10)->nullable();
            $table->string('civil_status', 20)->nullable();
            $table->string('citizenship', 50)->nullable();
            $table->string('c_category', 30)->nullable();
            $table->string('country', 50)->nullable();

            // ── Physical ──────────────────────────────────────────────────────
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('height_m', 5, 2)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('weight_lb', 5, 2)->nullable();
            $table->string('b_type', 5)->nullable();

            // ── Government IDs ────────────────────────────────────────────────
            $table->string('gsis')->nullable();
            $table->string('pagibig')->nullable();
            $table->string('philhealth')->nullable();
            $table->string('sss')->nullable();
            $table->string('tin')->nullable();

            // ── Contact ───────────────────────────────────────────────────────
            $table->string('telephone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('org_email')->nullable()->index();
            $table->string('email')->nullable();

            // ── Residential Address ───────────────────────────────────────────
            $table->string('add_block')->nullable();
            $table->string('add_street')->nullable();
            $table->string('add_village')->nullable();
            $table->string('add_brgy')->nullable();
            $table->string('add_city')->nullable();
            $table->string('add_region')->nullable();
            $table->string('add_prov')->nullable();
            $table->string('add_zcode')->nullable();

            // ── Permanent Address ─────────────────────────────────────────────
            $table->string('padd_block')->nullable();
            $table->string('padd_street')->nullable();
            $table->string('padd_village')->nullable();
            $table->string('padd_brgy')->nullable();
            $table->string('padd_city')->nullable();
            $table->string('padd_region')->nullable();
            $table->string('padd_prov')->nullable();
            $table->string('padd_zcode')->nullable();

            // ── Leave Credits ─────────────────────────────────────────────────
            $table->decimal('sl', 8, 3)->default(0);
            $table->decimal('vl', 8, 3)->default(0);
            $table->decimal('mat_leave', 8, 3)->default(0);
            $table->decimal('special_pl', 8, 3)->default(0);
            $table->decimal('solo_pl', 8, 3)->default(0);
            $table->decimal('study_leave', 8, 3)->default(0);
            $table->decimal('vawc_leave', 8, 3)->default(0);
            $table->decimal('rehab_leave', 8, 3)->default(0);
            $table->decimal('benefits_leave', 8, 3)->default(0);
            $table->decimal('calamity_leave', 8, 3)->default(0);
            $table->decimal('adopt_leave', 8, 3)->default(0);
            $table->decimal('servcred_leave', 8, 3)->default(0);
            $table->decimal('well_leave', 8, 3)->default(0);

            // ── Auth ──────────────────────────────────────────────────────────
            $table->string('verification_code')->nullable();
            $table->string('password')->nullable();
            $table->string('role', 50)->nullable();         // employee role label
            $table->string('android_id')->nullable();

            // ── Profile / Documents ───────────────────────────────────────────
            $table->string('profile')->nullable();          // profile photo path
            $table->longText('esign')->nullable();          // encrypted e-signature image data
            $table->tinyInteger('dpn')->default(0);         // data privacy notice accepted
            $table->tinyInteger('stat_1')->default(1);      // account status: 1=active, 0=suspended

            $table->timestamps();

            $table->index(['emp_dept', 'emp_status']);
            $table->index('camp_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
