<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipcr_mfo_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ipcr_mfo_id')->index();
            $table->unsignedBigInteger('dpcr_mfo_data_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('mfo')->nullable();
            $table->text('target')->nullable();
            $table->string('measure')->nullable();
            $table->text('in_support')->nullable();
            $table->text('report_sup')->nullable();
            $table->string('alloted')->nullable();
            $table->string('div_account')->nullable();
            $table->decimal('quality', 5, 2)->nullable();
            $table->decimal('q_score', 5, 2)->nullable();
            $table->decimal('efficiency', 5, 2)->nullable();
            $table->decimal('e_score', 5, 2)->nullable();
            $table->decimal('timeliness', 5, 2)->nullable();
            $table->decimal('t_score', 5, 2)->nullable();
            $table->decimal('average', 5, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->string('category', 30)->nullable();
            $table->string('dpcr_by')->nullable();
            $table->integer('order')->default(0);
            $table->tinyInteger('lock')->default(0);
            $table->timestamps();

            $table->foreign('ipcr_mfo_id')->references('id')->on('ipcr_mfos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipcr_mfo_data');
    }
};
