<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('office_name');
            $table->string('office_abbr', 30)->nullable();
            $table->string('office_code', 20)->nullable()->unique();   // e.g. "HRMO", "REG", "FIN"
            $table->unsignedBigInteger('office_head_id')->nullable();  // → employees.id
            $table->unsignedBigInteger('oic_id')->nullable();          // → employees.id (OIC)
            $table->timestamps();

            $table->index('office_head_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
