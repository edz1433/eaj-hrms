<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pmts', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->index();
            $table->string('position')->nullable();
            $table->string('role', 50)->nullable();     // opcr|dpcr|ipcr
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pmts');
    }
};
