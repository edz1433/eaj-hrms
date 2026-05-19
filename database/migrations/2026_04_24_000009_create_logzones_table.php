<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logzones', function (Blueprint $table) {
            $table->id();
            $table->text('points');               // GPS polygon coordinates (JSON/WKT)
            $table->unsignedBigInteger('camp_id')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logzones');
    }
};
