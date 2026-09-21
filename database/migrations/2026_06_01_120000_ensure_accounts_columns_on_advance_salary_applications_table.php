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
