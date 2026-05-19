<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gov_ids', function (Blueprint $table) {
            $table->id();
            $table->string('empid')->unique()->index();
            // govid stores JSON array of {type, number, issue_date, issue_place}
            $table->text('govid')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gov_ids');
    }
};
