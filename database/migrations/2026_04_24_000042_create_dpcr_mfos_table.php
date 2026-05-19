<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpcr_mfos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dpcr_id')->index();
            $table->unsignedBigInteger('opcr_id')->nullable()->index();
            $table->string('mfo')->nullable();
            $table->string('functions')->nullable();
            $table->decimal('percent', 5, 2)->nullable();
            $table->integer('count')->default(0);
            $table->timestamps();

            $table->foreign('dpcr_id')->references('id')->on('dpcrs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpcr_mfos');
    }
};
