<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_infos', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->unique()->index();
            $table->text('skills_hob')->nullable();      // skills & hobbies (comma-sep)
            $table->text('recognition')->nullable();     // awards / recognitions (comma-sep)
            $table->text('mem_org')->nullable();         // membership in organisations (comma-sep)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_infos');
    }
};
