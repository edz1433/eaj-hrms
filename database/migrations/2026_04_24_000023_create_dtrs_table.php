<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dtrs', function (Blueprint $table) {
            $table->id();
            $table->string('emp_ID')->index();
            $table->string('device_id')->nullable();          // biometric device
            $table->string('device_id_in')->nullable();
            $table->string('device_id_out')->nullable();
            $table->string('device_id_over')->nullable();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->time('time_over')->nullable();            // overtime out
            $table->date('date')->index();
            // No timestamps — this table is high-volume, insert-only

            $table->unique(['emp_ID', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtrs');
    }
};
