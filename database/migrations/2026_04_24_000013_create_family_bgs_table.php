<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_bgs', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->index();           // references employees.emp_ID

            // Spouse
            $table->string('spouse_sname')->nullable();
            $table->string('spouse_fname')->nullable();
            $table->string('spouse_mname')->nullable();
            $table->string('spouse_ext', 10)->nullable();
            $table->string('occupation')->nullable();
            $table->string('bus_name')->nullable();
            $table->string('bus_address')->nullable();
            $table->string('telephone')->nullable();

            // Children (stored as comma-separated, one row per employee)
            $table->text('name_child')->nullable();
            $table->text('date_birth')->nullable();

            // Father
            $table->string('father_sname')->nullable();
            $table->string('father_fname')->nullable();
            $table->string('father_mname')->nullable();
            $table->string('father_ext', 10)->nullable();

            // Mother
            $table->string('mother_maiden')->nullable();
            $table->string('mother_sname')->nullable();
            $table->string('mother_fname')->nullable();
            $table->string('mother_mname')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_bgs');
    }
};
