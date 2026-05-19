<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpcrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('opcr_id')->nullable()->index();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('pr_number')->unique();
            $table->string('op_pr_number')->nullable()->index(); // linked OPCR pr_number
            $table->string('mfo')->nullable();
            $table->decimal('percent', 5, 2)->nullable();
            $table->year('year')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpcrs');
    }
};
