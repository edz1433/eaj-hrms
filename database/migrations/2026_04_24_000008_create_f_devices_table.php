<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('f_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->unsignedBigInteger('camp_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('f_devices');
    }
};
