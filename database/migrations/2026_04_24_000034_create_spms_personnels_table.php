<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spms_personnels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empid')->index();      // references employees.id
            $table->tinyInteger('category')->nullable();        // 1=office head,2=faculty,3=staff
            $table->unsignedBigInteger('off_coll_id')->nullable(); // office/college id
            $table->string('position')->nullable();
            $table->string('emp_position')->nullable();
            $table->string('designation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spms_personnels');
    }
};
