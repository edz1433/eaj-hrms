<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligibilities', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->index();
            $table->string('careereligible')->nullable();   // eligibility name / exam title
            $table->string('rating')->nullable();           // exam rating / score
            $table->date('date_exam')->nullable();
            $table->string('place_exam')->nullable();
            $table->string('number')->nullable();           // license/certificate number
            $table->date('date_valid')->nullable();         // validity date
            $table->string('attachment')->nullable();       // file path
            $table->tinyInteger('status')->default(0);     // 0=pending,1=approved,2=cancelled
            $table->text('remarks')->nullable();

            $table->index(['empid', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibilities');
    }
};
