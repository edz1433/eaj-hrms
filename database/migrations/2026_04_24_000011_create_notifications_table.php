<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->nullable()->index();    // recipient employee id
            $table->unsignedBigInteger('lapp_id')->nullable();   // leave application id
            $table->unsignedBigInteger('esign_id')->nullable();  // e-signature request id
            $table->string('category', 50)->nullable();     // leave|eligibility|workexp|etc
            $table->string('utype', 30)->nullable();        // who triggered: admin|employee
            $table->string('module', 50)->nullable();       // source module
            $table->tinyInteger('status')->default(0);      // 0=unread, 1=read
            $table->timestamps();

            $table->index(['empid', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
