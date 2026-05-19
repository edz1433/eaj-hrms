<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // Leadership positions (stores employee id)
            $table->unsignedBigInteger('suc_pres')->nullable();
            $table->unsignedBigInteger('vpaa')->nullable();
            $table->unsignedBigInteger('vpaf')->nullable();
            $table->unsignedBigInteger('hr')->nullable();
            // Time & Attendance
            $table->string('dtr_acct')->nullable();        // comma-sep employee ids with full DTR access
            $table->string('hr_kiosk')->nullable();        // comma-sep emp_IDs with kiosk access
            $table->string('hrk_pw')->nullable();          // kiosk password
            $table->boolean('sync_backups')->default(false);
            $table->tinyInteger('te_rstrct_lvl')->default(0); // 0=none,1=partial,2=full
            // Emails
            $table->string('records_office_email')->nullable();
            $table->string('job_portal_email')->nullable();
            $table->string('hr_head_email')->nullable();
            // System controls
            $table->boolean('maintenance')->default(false);
            $table->string('system_name')->default('CPSU HRIS');
            // Theme
            $table->string('theme', 30)->default('ea');
            $table->string('primary_color', 20)->nullable();
            $table->string('accent_color', 20)->nullable();
            $table->string('login_bg')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
