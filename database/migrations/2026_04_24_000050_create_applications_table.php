<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jid')->index();     // references job_hirings.id
            $table->string('app_number')->nullable()->unique();
            $table->string('position')->nullable();

            // Applicant info
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->tinyInteger('age')->nullable();
            $table->string('sex', 10)->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();

            // Qualifications summary
            $table->text('education')->nullable();
            $table->text('eligibility')->nullable();

            // Uploaded documents (file paths)
            $table->string('pds')->nullable();
            $table->string('wes')->nullable();              // work experience sheet
            $table->string('intent')->nullable();           // letter of intent
            $table->string('resume')->nullable();
            $table->string('tor')->nullable();              // transcript of records
            $table->string('coe')->nullable();              // certificate of employment
            $table->string('cert_training')->nullable();

            // Processing
            $table->text('dq_reason')->nullable();          // disqualification reason
            $table->string('ctrl_no')->nullable();
            $table->dateTime('interview_datetime')->nullable();
            $table->string('venue')->nullable();
            $table->tinyInteger('status')->default(0);      // 0=pending,1=qualified,2=dq,3=hired
            $table->tinyInteger('is_complete')->default(0); // 1=requirements complete

            $table->timestamps();

            $table->foreign('jid')->references('id')->on('job_hirings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
