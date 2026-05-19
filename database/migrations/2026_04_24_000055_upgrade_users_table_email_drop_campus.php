<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Upgrade path for EXISTING installations:
 * - Renames  username  →  email   (users now log in with their e-mail)
 * - Drops    campus_id            (campus moved out of users; managed per-employee)
 *
 * For fresh installs migration 000006 already creates the table correctly —
 * this migration will simply find the column already gone/renamed and skip.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename username → email only if username still exists
            if (Schema::hasColumn('users', 'username') && !Schema::hasColumn('users', 'email')) {
                $table->renameColumn('username', 'email');
            }

            // Add email column if it was never there
            if (!Schema::hasColumn('users', 'email')) {
                $table->string('email')->unique()->after('lname');
            }

            // Drop campus_id if present
            if (Schema::hasColumn('users', 'campus_id')) {
                $table->dropColumn('campus_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'email') && !Schema::hasColumn('users', 'username')) {
                $table->renameColumn('email', 'username');
            }
            if (!Schema::hasColumn('users', 'campus_id')) {
                $table->unsignedBigInteger('campus_id')->nullable()->after('gender');
            }
        });
    }
};
