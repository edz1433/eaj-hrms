<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_face_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->index();
            $table->text('qr_token');
            $table->longText('face_embedding');
            $table->unsignedTinyInteger('scan_count')->default(0);
            $table->unsignedBigInteger('registered_by')->nullable()->index();
            $table->timestamp('registered_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['employee_id', 'is_active'], 'employee_face_profiles_active_unique');
        });

        Schema::create('time_entry_logs', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable()->index();
            $table->text('qr_token')->nullable();
            $table->string('selected_action', 40)->nullable();
            $table->string('status', 20)->index();
            $table->text('reason')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->decimal('matched_score', 8, 6)->nullable();
            $table->json('device_info')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('logged_at')->nullable()->index();
            $table->timestamps();

            $table->index(['employee_id', 'logged_at']);
            $table->index(['status', 'created_at']);
        });

        DB::table('menu_settings')->where('menu_key', 'time_entry')->delete();
        DB::table('menu_settings')->updateOrInsert(
            ['menu_key' => 'face_registration'],
            [
                'label' => 'Face Registration',
                'group' => 'HR Management',
                'sort_order' => 3,
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('menu_settings')->whereIn('menu_key', ['time_entry', 'face_registration'])->delete();
        Schema::dropIfExists('time_entry_logs');
        Schema::dropIfExists('employee_face_profiles');
    }
};
