<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pr_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pr_id')->index();  // references opcrs/dpcrs/ipcrs id
            $table->string('mfo')->nullable();
            $table->text('target')->nullable();
            $table->text('in_support')->nullable();
            $table->text('report_sup')->nullable();
            $table->string('alloted')->nullable();
            $table->string('div_account')->nullable();
            $table->decimal('q', 5, 2)->nullable();        // quality rating
            $table->decimal('e', 5, 2)->nullable();        // efficiency rating
            $table->decimal('t', 5, 2)->nullable();        // timeliness rating
            $table->decimal('qrate', 5, 2)->nullable();
            $table->decimal('erate', 5, 2)->nullable();
            $table->decimal('trate', 5, 2)->nullable();
            $table->decimal('a', 5, 2)->nullable();        // average
            $table->text('remarks')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pr_data');
    }
};
