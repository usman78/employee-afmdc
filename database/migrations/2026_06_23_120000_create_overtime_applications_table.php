<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('OVERTIME_APPLICATIONS')) {
            Schema::create('OVERTIME_APPLICATIONS', function (Blueprint $table) {
                $table->integer('ID')->primary();
                $table->string('EMP_CODE', 20)->index();
                $table->string('SALARY_MONTH', 7)->index();
                $table->date('OVERTIME_DATE')->index();
                $table->decimal('GROSS_SALARY', 12, 2);
                $table->integer('OVERTIME_MINUTES');
                $table->decimal('HOURLY_RATE', 12, 2);
                $table->decimal('CALCULATED_AMOUNT', 12, 2);
                $table->integer('SANCTIONED_MINUTES')->nullable();
                $table->decimal('SANCTIONED_AMOUNT', 12, 2)->nullable();
                $table->timestamp('SHIFT_START')->nullable();
                $table->timestamp('SHIFT_END')->nullable();
                $table->timestamp('TIME_IN')->nullable();
                $table->timestamp('TIME_OUT')->nullable();
                $table->string('IS_HOLIDAY', 1)->default('N');
                $table->string('IS_SECURITY', 1)->default('N');
                $table->text('REMARKS');
                $table->string('STATUS', 30)->default('pending')->index();
                $table->string('HOD_APPROVED_BY', 20)->nullable();
                $table->timestamp('HOD_APPROVED_AT')->nullable();
                $table->text('HOD_REMARKS')->nullable();
                $table->string('HR_APPROVED_BY', 20)->nullable();
                $table->timestamp('HR_APPROVED_AT')->nullable();
                $table->text('HR_REMARKS')->nullable();
                $table->string('FINANCE_APPROVED_BY', 20)->nullable();
                $table->timestamp('FINANCE_APPROVED_AT')->nullable();
                $table->text('FINANCE_REMARKS')->nullable();
                $table->timestamp('APPLIED_AT')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('OVERTIME_APPLICATIONS');
    }
};
