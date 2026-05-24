<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $employeeIdColumns = [
        'employees' => ['emp_ID'],
        'users' => ['emp_ID'],
        'dtrs' => ['emp_ID'],
        'dtrs_test' => ['emp_ID'],
        'sync_feeds' => ['emp_ID'],
        'tardinesses' => ['emp_ID'],
        'educ_bgs' => ['empid'],
        'eligibilities' => ['empid'],
        'event_logs' => ['empid'],
        'family_bgs' => ['empid'],
        'flexis' => ['empid'],
        'gov_ids' => ['empid'],
        'histories' => ['empid'],
        'info_questions' => ['empid'],
        'learning_devs' => ['empid'],
        'leave_applications' => ['empid'],
        'leave_credits' => ['empid'],
        'notifications' => ['empid', 'notifempid'],
        'official_times' => ['empid'],
        'other_infos' => ['empid'],
        'pds_references' => ['empid'],
        'voluntary_works' => ['empid'],
        'work_experiences' => ['empid'],
    ];

    public function up(): void
    {
        if (!Schema::hasTable('employees')) {
            return;
        }

        $legacyIds = DB::table('employees')
            ->where('emp_ID', 'regexp', '^[0-9]+$')
            ->pluck('emp_ID')
            ->unique()
            ->values();

        foreach ($legacyIds as $legacyId) {
            $newId = 'EMP' . str_pad((string) ((int) $legacyId), 4, '0', STR_PAD_LEFT);
            $this->replaceEmployeeId((string) $legacyId, $newId);
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->update(['employee_id_prefix' => 'EMP']);
        }
    }

    public function down(): void
    {
        // One-way data cleanup. Existing records should keep the configured EMP-prefixed IDs.
    }

    private function replaceEmployeeId(string $oldId, string $newId): void
    {
        foreach ($this->employeeIdColumns as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                if ($table === 'employees' && $column === 'emp_ID') {
                    $targetExists = DB::table('employees')->where('emp_ID', $newId)->exists();

                    if ($targetExists) {
                        continue;
                    }
                }

                DB::table($table)
                    ->where($column, $oldId)
                    ->update([$column => $newId]);
            }
        }
    }
};
