<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('OVERTIME_APPLICATIONS')) {
            return;
        }

        Schema::table('OVERTIME_APPLICATIONS', function (Blueprint $table) {
            if (! Schema::hasColumn('OVERTIME_APPLICATIONS', 'AUDITED_BY')) {
                $table->string('AUDITED_BY', 20)->nullable();
            }

            if (! Schema::hasColumn('OVERTIME_APPLICATIONS', 'AUDITED_AT')) {
                $table->timestamp('AUDITED_AT')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('OVERTIME_APPLICATIONS')) {
            return;
        }

        Schema::table('OVERTIME_APPLICATIONS', function (Blueprint $table) {
            foreach (['AUDITED_BY', 'AUDITED_AT'] as $column) {
                if (Schema::hasColumn('OVERTIME_APPLICATIONS', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
