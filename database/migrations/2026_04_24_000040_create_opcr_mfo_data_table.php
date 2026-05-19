<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opcr_mfo_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('opcr_mfo_id')->index();
            $table->string('mfo')->nullable();
            $table->text('target')->nullable();
            $table->string('measure')->nullable();
            $table->text('link_source')->nullable();
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
            $table->decimal('a', 5, 2)->nullable();          // average score
            $table->text('remarks')->nullable();
            $table->string('category', 30)->nullable();      // core|strategic|support
            $table->string('opcr_by')->nullable();
            $table->integer('order')->default(0);
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('opcr_mfo_id')->references('id')->on('opcr_mfos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opcr_mfo_data');
    }
};
