<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camp_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('abbr', 50)->nullable();
            $table->string('code', 30)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camp_branches');
    }
};
