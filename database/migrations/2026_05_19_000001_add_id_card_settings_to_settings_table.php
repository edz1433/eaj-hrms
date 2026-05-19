<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'id_card_template')) {
                $table->string('id_card_template', 30)->default('classic')->after('login_bg');
            }

            if (!Schema::hasColumn('settings', 'id_card_primary_color')) {
                $table->string('id_card_primary_color', 20)->nullable()->after('id_card_template');
            }

            if (!Schema::hasColumn('settings', 'id_card_accent_color')) {
                $table->string('id_card_accent_color', 20)->nullable()->after('id_card_primary_color');
            }

            if (!Schema::hasColumn('settings', 'id_card_logo')) {
                $table->string('id_card_logo')->nullable()->after('id_card_accent_color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['id_card_logo', 'id_card_accent_color', 'id_card_primary_color', 'id_card_template'] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
