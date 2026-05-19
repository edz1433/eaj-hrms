<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->string('transnum')->nullable()->index();   // transaction / reference number
            $table->string('empid')->index();                  // references employees.emp_ID
            $table->string('position')->nullable();
            $table->decimal('salary', 10, 2)->nullable();

            // Leave details
            $table->string('leave_type')->nullable();
            $table->string('leave_purpose')->nullable();
            $table->text('leave_detail')->nullable();
            $table->string('date_range')->nullable();          // "YYYY-MM-DD to YYYY-MM-DD"
            $table->decimal('days', 8, 3)->nullable();
            $table->string('commutation', 10)->nullable();     // requested|not requested

            // Credit balances at time of filing
            $table->decimal('total_vl', 8, 3)->nullable();
            $table->decimal('total_sl', 8, 3)->nullable();
            $table->decimal('less_vl', 8, 3)->nullable();
            $table->decimal('less_sl', 8, 3)->nullable();
            $table->decimal('earn', 8, 3)->nullable();
            $table->decimal('less', 8, 3)->nullable();
            $table->decimal('balance', 8, 3)->nullable();
            $table->decimal('day_wpay', 8, 3)->nullable();

            // Approval chain
            $table->tinyInteger('recommend')->default(0);      // 1=recommended
            $table->tinyInteger('emp_esign')->default(0);
            $table->string('supervisor')->nullable();
            $table->string('oic')->nullable();
            $table->string('sup_prefix')->nullable();
            $table->text('sup_sign')->nullable();
            $table->timestamp('sup_sdate')->nullable();
            $table->string('president')->nullable();
            $table->string('pres_prefix')->nullable();
            $table->text('pres_sign')->nullable();
            $table->timestamp('pres_sdate')->nullable();
            $table->string('hr')->nullable();
            $table->string('hr_prefix')->nullable();
            $table->text('hr_sign')->nullable();
            $table->timestamp('hr_sdate')->nullable();

            // Remarks
            $table->string('remarks_stat')->nullable();
            $table->text('remarks_details')->nullable();
            $table->text('remarks_details1')->nullable();
            $table->text('remarks_details2')->nullable();

            // Meta
            $table->unsignedBigInteger('department')->nullable()->index();  // office id
            $table->date('date_filing')->nullable();
            $table->tinyInteger('status')->default(1);          // 1=pending,2=approved,3=disapproved,4=returned
            $table->tinyInteger('history')->default(1);         // 1=active in history
            $table->tinyInteger('gen_app')->default(0);         // generated application
            $table->string('as_of')->nullable();
            $table->tinyInteger('holiday')->default(0);

            $table->timestamps();

            $table->index(['empid', 'status', 'history']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};
