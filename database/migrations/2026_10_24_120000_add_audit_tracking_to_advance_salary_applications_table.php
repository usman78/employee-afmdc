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
            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'AUDITED_BY')) {
                $table->string('AUDITED_BY', 20)->nullable();
            }

            if (! Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', 'AUDITED_AT')) {
                $table->timestamp('AUDITED_AT')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ADVANCE_SALARY_APPLICATIONS')) {
            return;
        }

        Schema::table('ADVANCE_SALARY_APPLICATIONS', function (Blueprint $table) {
            foreach (['AUDITED_BY', 'AUDITED_AT'] as $column) {
                if (Schema::hasColumn('ADVANCE_SALARY_APPLICATIONS', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
