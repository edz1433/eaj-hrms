<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_feeds', function (Blueprint $table) {
            $table->id();
            $table->string('emp_ID')->nullable()->index();
            $table->string('device_id')->nullable();
            $table->string('punch_type', 20)->nullable();  // IN | OUT | OT
            $table->timestamp('punched_at')->nullable();
            $table->date('date')->nullable();
            $table->tinyInteger('synced')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_feeds');
    }
};
