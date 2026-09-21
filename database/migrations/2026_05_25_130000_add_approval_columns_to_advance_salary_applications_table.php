<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ADVANCE_SALARY_APPLICATIONS')) {
            return;
        }

        Schema::table('ADVANCE_SALARY_APPLICATIONS', function (Blueprint $table) {
            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'SANCTIONED_AMOUNT')) {
                $table->decimal('SANCTIONED_AMOUNT', 12, 2)->nullable();
            }

            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'HOD_APPROVED_BY')) {
                $table->string('HOD_APPROVED_BY', 20)->nullable();
            }

            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'HOD_APPROVED_AT')) {
                $table->timestamp('HOD_APPROVED_AT')->nullable();
            }

            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'HOD_REMARKS')) {
                $table->text('HOD_REMARKS')->nullable();
            }

            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'HR_APPROVED_BY')) {
                $table->string('HR_APPROVED_BY', 20)->nullable();
            }

            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'HR_APPROVED_AT')) {
                $table->timestamp('HR_APPROVED_AT')->nullable();
            }

            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'HR_REMARKS')) {
                $table->text('HR_REMARKS')->nullable();
            }

            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'ACCOUNTS_APPROVED_BY')) {
                $table->string('ACCOUNTS_APPROVED_BY', 20)->nullable();
            }

            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'ACCOUNTS_APPROVED_AT')) {
                $table->timestamp('ACCOUNTS_APPROVED_AT')->nullable();
            }

            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'ACCOUNTS_REMARKS')) {
                $table->text('ACCOUNTS_REMARKS')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ADVANCE_SALARY_APPLICATIONS')) {
            return;
        }

        Schema::table('ADVANCE_SALARY_APPLICATIONS', function (Blueprint $table) {
            foreach ([
                'SANCTIONED_AMOUNT',
                'HOD_APPROVED_BY',
                'HOD_APPROVED_AT',
                'HOD_REMARKS',
                'HR_APPROVED_BY',
                'HR_APPROVED_AT',
                'HR_REMARKS',
                'ACCOUNTS_APPROVED_BY',
                'ACCOUNTS_APPROVED_AT',
                'ACCOUNTS_REMARKS',
            ] as $column) {
                if (Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
