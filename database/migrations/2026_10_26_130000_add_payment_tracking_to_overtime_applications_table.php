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
            if (! Schema::hasColumn('OVERTIME_APPLICATIONS', 'PAID_BY')) {
                $table->string('PAID_BY', 20)->nullable();
            }

            if (! Schema::hasColumn('OVERTIME_APPLICATIONS', 'PAID_AT')) {
                $table->timestamp('PAID_AT')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('OVERTIME_APPLICATIONS')) {
            return;
        }

        Schema::table('OVERTIME_APPLICATIONS', function (Blueprint $table) {
            foreach (['PAID_BY', 'PAID_AT'] as $column) {
                if (Schema::hasColumn('OVERTIME_APPLICATIONS', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
