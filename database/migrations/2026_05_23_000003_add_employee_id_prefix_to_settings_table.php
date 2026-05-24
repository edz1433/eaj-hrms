<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'employee_id_prefix')) {
                $table->string('employee_id_prefix', 20)->default('EMP')->after('system_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'employee_id_prefix')) {
                $table->dropColumn('employee_id_prefix');
            }
        });
    }
};
