<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pr_settings', function (Blueprint $table) {
            $table->id();
            $table->string('category', 30)->unique();    // core|strategic|support
            $table->decimal('core_mfo1', 5, 2)->default(0);
            $table->decimal('core_mfo2', 5, 2)->default(0);
            $table->decimal('core_mfo3', 5, 2)->default(0);
            $table->decimal('core_sum', 5, 2)->default(0);
            $table->decimal('strategic_mfo4', 5, 2)->default(0);
            $table->decimal('strategic_mfo5', 5, 2)->default(0);
            $table->decimal('strat_sum', 5, 2)->default(0);
            $table->decimal('support_mfo4', 5, 2)->default(0);
            $table->decimal('support_mfo5', 5, 2)->default(0);
            $table->decimal('support_sum', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pr_settings');
    }
};
