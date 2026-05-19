<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tardinesses', function (Blueprint $table) {
            $table->id();
            $table->string('emp_ID')->index();
            $table->date('date')->index();
            $table->integer('minutes_late')->default(0);
            $table->integer('minutes_undertime')->default(0);
            $table->integer('absences')->default(0);       // absent days (0 or 1)
            $table->string('month', 20)->nullable();       // e.g. "January 2025"
            $table->year('year')->nullable();
            $table->tinyInteger('month_num')->nullable();  // 1-12
            $table->timestamps();

            $table->unique(['emp_ID', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tardinesses');
    }
};
