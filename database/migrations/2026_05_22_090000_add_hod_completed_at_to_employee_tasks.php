<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('EMPLOYEE_TASKS') && ! Schema::hasColumn('EMPLOYEE_TASKS', 'HOD_COMPLETED_AT')) {
            Schema::table('EMPLOYEE_TASKS', function (Blueprint $table) {
                $table->timestamp('HOD_COMPLETED_AT')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('EMPLOYEE_TASKS') && Schema::hasColumn('EMPLOYEE_TASKS', 'HOD_COMPLETED_AT')) {
            Schema::table('EMPLOYEE_TASKS', function (Blueprint $table) {
                $table->dropColumn('HOD_COMPLETED_AT');
            });
        }
    }
};
