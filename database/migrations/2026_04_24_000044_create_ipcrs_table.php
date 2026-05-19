<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipcrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->unsignedBigInteger('dpcr_id')->nullable()->index();
            $table->string('pr_number')->unique();
            $table->string('dp_pr_number')->nullable()->index(); // linked DPCR pr_number
            $table->string('mfo')->nullable();
            $table->decimal('percent', 5, 2)->nullable();
            $table->year('year')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipcrs');
    }
};
