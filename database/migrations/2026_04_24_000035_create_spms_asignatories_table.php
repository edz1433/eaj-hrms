<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spms_asignatories', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->index();               // employee id
            $table->string('pr_number')->nullable()->index();
            $table->string('suffixes')->nullable();
            $table->string('designation')->nullable();
            $table->string('spms_type', 20)->nullable();    // opcr|dpcr|ipcr
            $table->string('label')->nullable();
            // No timestamps — SpmsAsignatory model sets $timestamps = false
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spms_asignatories');
    }
};
