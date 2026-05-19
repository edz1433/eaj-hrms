<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('official_times', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->unique()->index();

            // Morning / Afternoon schedule per weekday
            $table->time('morn_mon')->nullable();
            $table->time('aft_mon')->nullable();
            $table->time('morn_tue')->nullable();
            $table->time('aft_tue')->nullable();
            $table->time('morn_wed')->nullable();
            $table->time('aft_wed')->nullable();
            $table->time('morn_thu')->nullable();
            $table->time('aft_thu')->nullable();
            $table->time('morn_fri')->nullable();
            $table->time('aft_fri')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('official_times');
    }
};
