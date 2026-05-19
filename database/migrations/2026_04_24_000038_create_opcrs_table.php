<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opcrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('pr_number')->unique();
            $table->string('mfo')->nullable();
            $table->decimal('percent', 5, 2)->nullable();
            $table->year('year')->nullable();
            $table->tinyInteger('status')->default(0);   // 0=draft,1=submitted,2=rated,3=approved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opcrs');
    }
};
