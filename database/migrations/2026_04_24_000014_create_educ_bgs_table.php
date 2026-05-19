<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educ_bgs', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->index();

            // Elementary
            $table->string('elem_school')->nullable();
            $table->string('elem_period')->nullable();
            $table->string('elem_level')->nullable();
            $table->string('elem_grad')->nullable();
            $table->string('elem_honor')->nullable();

            // Secondary
            $table->string('sec_school')->nullable();
            $table->string('sec_period')->nullable();
            $table->string('sec_level')->nullable();
            $table->string('sec_grad')->nullable();
            $table->string('sec_honor')->nullable();

            // Vocational / Trade
            $table->string('voc_school')->nullable();
            $table->string('voc_course')->nullable();
            $table->string('voc_period')->nullable();
            $table->string('voc_level')->nullable();
            $table->string('voc_grad')->nullable();
            $table->string('voc_honor')->nullable();

            // College
            $table->string('coll_school')->nullable();
            $table->string('coll_course')->nullable();
            $table->string('coll_period')->nullable();
            $table->string('coll_level')->nullable();
            $table->string('coll_grad')->nullable();
            $table->string('coll_honor')->nullable();

            // Graduate Studies
            $table->string('grad_school')->nullable();
            $table->string('grad_course')->nullable();
            $table->string('grad_period')->nullable();
            $table->string('grad_level')->nullable();
            $table->string('grad_grad')->nullable();
            $table->string('grad_honor')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educ_bgs');
    }
};
