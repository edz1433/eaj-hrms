<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE employees MODIFY esign LONGTEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE employees MODIFY esign TEXT NULL');
    }
};
