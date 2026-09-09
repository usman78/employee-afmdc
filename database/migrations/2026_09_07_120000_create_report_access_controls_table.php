<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('REPORT_ACCESS_CONTROLS')) {
            Schema::create('REPORT_ACCESS_CONTROLS', function (Blueprint $table) {
                $table->string('EMP_CODE', 20);
                $table->string('REPORT_KEY', 100);
                $table->string('IS_ALLOWED', 1)->default('N');
                $table->string('UPDATED_BY', 20)->nullable();
                $table->timestamp('CREATED_AT')->nullable();
                $table->timestamp('UPDATED_AT')->nullable();
                $table->unique(['EMP_CODE', 'REPORT_KEY'], 'REP_ACCESS_CTRL_UNQ');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('REPORT_ACCESS_CONTROLS');
    }
};
