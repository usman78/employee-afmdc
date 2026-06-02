<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ADVANCE_SALARY_APPLICATIONS')) {
            Schema::create('ADVANCE_SALARY_APPLICATIONS', function (Blueprint $table) {
                $table->integer('ID')->primary();
                $table->string('EMP_CODE', 20)->index();
                $table->string('SALARY_MONTH', 7)->index();
                $table->decimal('GROSS_SALARY', 12, 2);
                $table->decimal('MAX_AMOUNT', 12, 2);
                $table->decimal('REQUESTED_AMOUNT', 12, 2);
                $table->decimal('SANCTIONED_AMOUNT', 12, 2)->nullable();
                $table->integer('ELIGIBLE_DAYS');
                $table->text('REASON');
                $table->string('STATUS', 30)->default('pending')->index();
                $table->string('HOD_APPROVED_BY', 20)->nullable();
                $table->timestamp('HOD_APPROVED_AT')->nullable();
                $table->text('HOD_REMARKS')->nullable();
                $table->string('HR_APPROVED_BY', 20)->nullable();
                $table->timestamp('HR_APPROVED_AT')->nullable();
                $table->text('HR_REMARKS')->nullable();
                $table->string('ACCOUNTS_APPROVED_BY', 20)->nullable();
                $table->timestamp('ACCOUNTS_APPROVED_AT')->nullable();
                $table->text('ACCOUNTS_REMARKS')->nullable();
                $table->timestamp('APPLIED_AT')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ADVANCE_SALARY_APPLICATIONS');
    }
};
