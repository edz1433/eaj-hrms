<?php

use App\Models\UserMenuPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('menu_settings')
            ->where('menu_key', 'deans_list')
            ->delete();

        DB::table('menu_settings')->updateOrInsert(
            ['menu_key' => 'system_settings'],
            [
                'label' => 'System Settings',
                'group' => 'Administration',
                'sort_order' => 14,
                'is_visible' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        UserMenuPermission::query()
            ->whereJsonContains('menu_keys', 'deans_list')
            ->get()
            ->each(function (UserMenuPermission $permission): void {
                $permission->menu_keys = array_values(array_filter(
                    $permission->menu_keys ?? [],
                    fn (string $key): bool => $key !== 'deans_list'
                ));
                $permission->save();
            });

        $tables = [
            'ipcr_mfo_data',
            'ipcr_mfos',
            'ipcrs',
            'dpcr_mfo_data',
            'dpcr_mfos',
            'dpcrs',
            'opcr_mfo_data',
            'opcr_mfos',
            'opcrs',
            'evidence',
            'spms_comments',
            'spms_asignatories',
            'spms_personnels',
            'pr_data',
            'dpipops',
            'pr_settings',
            'pmts',
            'documents',
            'docu_folders',
            'deans',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table): void {
                foreach (['f1', 'f2', 'f3'] as $column) {
                    if (Schema::hasColumn('employees', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // The SPMS and Deans List modules were retired intentionally.
    }
};
