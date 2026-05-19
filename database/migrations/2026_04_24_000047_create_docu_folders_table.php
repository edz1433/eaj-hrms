<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docu_folders', function (Blueprint $table) {
            $table->id();
            $table->string('folder_name');
            $table->unsignedBigInteger('connected_folder')->nullable()->index(); // parent folder id
            $table->string('folder_category', 50)->nullable();    // mainfolder|subfolder
            $table->string('folder_path')->nullable();
            $table->text('office_access')->nullable();            // comma-sep office ids or "all"
            $table->tinyInteger('default_folder')->default(0);    // 1=system default
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docu_folders');
    }
};
