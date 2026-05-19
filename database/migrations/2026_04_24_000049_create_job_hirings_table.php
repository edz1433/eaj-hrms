<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_hirings', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->nullable();         // regular|casual|contract
            $table->string('title');                        // position title
            $table->string('plantilla_item_no')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('assignment')->nullable();        // office/campus assignment
            $table->text('education')->nullable();
            $table->text('eligibility')->nullable();
            $table->text('training')->nullable();
            $table->text('experience')->nullable();
            $table->text('competency')->nullable();
            $table->date('posted_at')->nullable();
            $table->date('expiration_at')->nullable();
            $table->tinyInteger('status')->default(1);      // 1=open,0=closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_hirings');
    }
};
