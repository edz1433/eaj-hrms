<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pds_references', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->unique()->index();
            $table->text('refname')->nullable();     // character references (comma-sep)
            $table->text('refadd')->nullable();      // reference addresses (comma-sep)
            $table->text('reftelno')->nullable();    // reference tel numbers (comma-sep)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pds_references');
    }
};
