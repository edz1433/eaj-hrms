<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PDS Section: "Questions" (civil/criminal/administrative answers)
        Schema::create('info_questions', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->unique()->index();
            $table->text('question')->nullable();    // comma-sep Yes/No answers per question
            $table->text('qdetails')->nullable();    // comma-sep detail text for each question
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_questions');
    }
};
