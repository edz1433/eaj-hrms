<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipcr_mfos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ipcr_id')->index();
            $table->unsignedBigInteger('dpcr_id')->nullable()->index();
            $table->string('mfo')->nullable();
            $table->decimal('percent', 5, 2)->nullable();
            $table->string('functions')->nullable();
            $table->integer('count')->default(0);
            $table->timestamps();

            $table->foreign('ipcr_id')->references('id')->on('ipcrs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipcr_mfos');
    }
};
