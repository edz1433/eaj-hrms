<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fname');
            $table->string('mname')->nullable();
            $table->string('lname');
            $table->string('gender', 10)->nullable();
            // email is the primary login credential (no username / no campus_id)
            $table->string('email')->unique();
            $table->string('verification_code')->nullable();
            $table->string('password');
            // Roles: Administrator | HR Administrator | Payroll Administrator | HR Staff | Payroll Staff
            $table->string('role', 50)->default('Staff');
            $table->text('access')->nullable();           // JSON or comma-sep additional access flags
            $table->string('emp_ID')->nullable()->index(); // FK to employees.emp_ID
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
