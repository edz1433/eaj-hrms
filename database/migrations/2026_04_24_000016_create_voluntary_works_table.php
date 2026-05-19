<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voluntary_works', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->index();
            $table->string('org_name')->nullable();
            $table->date('inc_date1')->nullable();
            $table->date('inc_date2')->nullable();
            $table->decimal('num_hours', 8, 2)->nullable();
            $table->string('position')->nullable();
            $table->string('attachment')->nullable();
            $table->tinyInteger('status')->default(0);   // 0=pending,1=approved,2=cancelled
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voluntary_works');
    }
};
