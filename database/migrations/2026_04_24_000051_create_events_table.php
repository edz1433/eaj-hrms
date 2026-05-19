<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('venue')->nullable();
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('campus_id')->nullable();
            $table->tinyInteger('emp_status')->nullable();    // which emp status can attend
            $table->string('bg_color', 20)->nullable();       // calendar color
            $table->string('org_dept')->nullable();           // organising department
            $table->string('remember_token', 100)->nullable();
            $table->tinyInteger('event_stat')->default(1);    // 1=active,0=cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
