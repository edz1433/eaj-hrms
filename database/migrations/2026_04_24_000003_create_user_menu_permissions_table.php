<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Runs before users table (000006) on fresh install, so no FK constraint here.
// Referential integrity is enforced in UserController which deletes permissions
// before deleting the user.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->index();
            $table->json('menu_keys')->nullable();   // e.g. ["dashboard","dtr","pds"]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_menu_permissions');
    }
};
