<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'morn_mon',
        'aft_mon',
        'morn_tue',
        'aft_tue',
        'morn_wed',
        'aft_wed',
        'morn_thu',
        'aft_thu',
        'morn_fri',
        'aft_fri',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('official_times')) {
            return;
        }

        foreach ($this->columns as $column) {
            if (Schema::hasColumn('official_times', $column)) {
                DB::statement("ALTER TABLE official_times MODIFY {$column} VARCHAR(17) NULL");
            }
        }

        $defaults = $this->defaults();
        $now = now();

        if (Schema::hasTable('employees')) {
            $existing = DB::table('official_times')->pluck('empid')->all();

            DB::table('employees')
                ->whereNotIn('emp_ID', $existing)
                ->orderBy('id')
                ->select('emp_ID')
                ->chunk(100, function ($employees) use ($defaults, $now) {
                    $rows = $employees->map(fn($employee) => array_merge(
                        ['empid' => $employee->emp_ID, 'created_at' => $now, 'updated_at' => $now],
                        $defaults
                    ))->all();

                    if (!empty($rows)) {
                        DB::table('official_times')->insert($rows);
                    }
                });
        }

        foreach ($defaults as $column => $value) {
            DB::table('official_times')
                ->whereNull($column)
                ->orWhere($column, '')
                ->update([$column => $value, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        // Keep the repaired employee schedules. The old TIME columns could not store AM/PM ranges.
    }

    private function defaults(): array
    {
        return [
            'morn_mon' => '08:00:00-12:00:00',
            'aft_mon' => '13:00:00-17:00:00',
            'morn_tue' => '08:00:00-12:00:00',
            'aft_tue' => '13:00:00-17:00:00',
            'morn_wed' => '08:00:00-12:00:00',
            'aft_wed' => '13:00:00-17:00:00',
            'morn_thu' => '08:00:00-12:00:00',
            'aft_thu' => '13:00:00-17:00:00',
            'morn_fri' => '08:00:00-12:00:00',
            'aft_fri' => '13:00:00-17:00:00',
        ];
    }
};
