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
            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'PAID_BY')) {
                $table->string('PAID_BY', 20)->nullable();
            }

            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'PAID_AT')) {
                $table->timestamp('PAID_AT')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ADVANCE_SALARY_APPLICATIONS')) {
            return;
        }

        Schema::table('ADVANCE_SALARY_APPLICATIONS', function (Blueprint $table) {
            foreach (['PAID_BY', 'PAID_AT'] as $column) {
                if (Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
