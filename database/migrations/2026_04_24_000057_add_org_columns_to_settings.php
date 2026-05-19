<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('org_name', 150)->nullable()->after('id');
            $table->string('sector', 20)->nullable()->default('government')->after('org_name');
            $table->string('org_type', 50)->nullable()->default('suc')->after('sector');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['org_name', 'sector', 'org_type']);
        });
    }
};
