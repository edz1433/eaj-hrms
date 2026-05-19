<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// This migration only runs on EXISTING installs that have a settings table
// without the theme columns. Fresh installs skip it — 000005 creates the
// table with all columns already included.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return; // fresh install — 000005 handles creation
        }

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'theme')) {
                $table->string('theme', 30)->default('ea')->after('maintenance');
            }
            if (!Schema::hasColumn('settings', 'primary_color')) {
                $table->string('primary_color', 20)->nullable()->after('theme');
            }
            if (!Schema::hasColumn('settings', 'accent_color')) {
                $table->string('accent_color', 20)->nullable()->after('primary_color');
            }
            if (!Schema::hasColumn('settings', 'login_bg')) {
                $table->string('login_bg')->nullable()->after('accent_color');
            }
            if (!Schema::hasColumn('settings', 'system_name')) {
                $table->string('system_name')->default('CPSU HRIS')->after('login_bg');
            }
            if (!Schema::hasColumn('settings', 'hr_head_email')) {
                $table->string('hr_head_email')->nullable()->after('system_name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['theme', 'primary_color', 'accent_color', 'login_bg', 'system_name', 'hr_head_email'],
                fn($col) => Schema::hasColumn('settings', $col)
            ));
        });
    }
};
