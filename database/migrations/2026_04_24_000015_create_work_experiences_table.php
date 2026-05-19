<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->index();
            $table->date('inc_date1')->nullable();           // date from
            $table->date('inc_date2')->nullable();           // date to
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('sg_grade')->nullable();          // salary grade
            $table->string('attachment')->nullable();        // file path
            $table->string('stat_app', 30)->nullable();      // status of appointment
            $table->tinyInteger('status')->default(0);       // 0=pending,1=approved,2=cancelled
            $table->string('service', 30)->nullable();       // gov|private
            $table->string('supervisor')->nullable();
            $table->text('list_accom')->nullable();
            $table->text('actual_summary')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['empid', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_experiences');
    }
};
