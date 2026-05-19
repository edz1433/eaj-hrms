<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_credits', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->index();
            $table->decimal('days', 8, 3)->default(0);
            $table->decimal('earn_sl', 8, 3)->default(0);
            $table->decimal('earn_vl', 8, 3)->default(0);
            $table->date('date')->nullable();
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('add_by')->nullable();  // user id who added
            $table->tinyInteger('stat')->default(1);            // 1=active,0=void
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_credits');
    }
};
