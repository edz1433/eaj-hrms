<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpipops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('off_id')->nullable();    // office id
            $table->string('pr_number')->nullable()->index();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('mfo')->nullable();
            $table->decimal('percent', 5, 2)->nullable();
            $table->string('cat', 30)->nullable();               // category
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpipops');
    }
};
