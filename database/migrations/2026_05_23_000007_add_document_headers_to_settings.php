<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'dtr_header')) {
                $table->string('dtr_header')->nullable()->after('id_card_logo');
            }

            if (!Schema::hasColumn('settings', 'leave_form_header')) {
                $table->string('leave_form_header')->nullable()->after('dtr_header');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'leave_form_header')) {
                $table->dropColumn('leave_form_header');
            }

            if (Schema::hasColumn('settings', 'dtr_header')) {
                $table->dropColumn('dtr_header');
            }
        });
    }
};
