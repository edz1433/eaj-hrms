<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('module', 80)->nullable();       // which module generated this
            $table->string('action', 50)->nullable();       // create|update|delete|approve|etc
            $table->text('description')->nullable();
            $table->string('reference_id')->nullable();     // ID of the affected record
            $table->string('reference_type')->nullable();   // model class name
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
