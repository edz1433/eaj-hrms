<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flexis', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->index();
            $table->date('date')->nullable();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->string('type', 20)->nullable();      // flexi|compressed|etc
            $table->text('reason')->nullable();
            $table->tinyInteger('status')->default(0);   // 0=pending,1=approved,2=disapproved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flexis');
    }
};
