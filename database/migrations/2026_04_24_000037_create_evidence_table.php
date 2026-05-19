<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->index();
            $table->string('category', 20)->nullable();     // opcr|dpcr|ipcr
            $table->unsignedBigInteger('data_id')->index(); // opcr_mfo_data id etc
            $table->string('title')->nullable();
            $table->string('evidence')->nullable();         // file path
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence');
    }
};
