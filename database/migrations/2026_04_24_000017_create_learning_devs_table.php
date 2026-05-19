<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_devs', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->index();
            $table->string('learning_dev')->nullable();      // title / program name
            $table->date('inc_date1')->nullable();
            $table->date('inc_date2')->nullable();
            $table->decimal('num_hours', 8, 2)->nullable();
            $table->string('types', 50)->nullable();         // foundation|managerial|technical|etc
            $table->string('conducted')->nullable();         // organiser/conductor
            $table->string('attachment')->nullable();
            $table->tinyInteger('status')->default(0);       // 0=pending,1=approved,2=cancelled
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_devs');
    }
};
