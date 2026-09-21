<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('EMPLOYEE_TASKS')) {
            Schema::create('EMPLOYEE_TASKS', function (Blueprint $table) {
                $table->integer('ID')->primary();
                $table->string('TITLE', 255);
                $table->text('DESCRIPTION')->nullable();
                $table->string('STATUS', 30)->default('pending');
                $table->string('PRIORITY', 20)->default('normal');
                $table->integer('PROGRESS')->default(0);
                $table->date('DUE_DATE')->nullable();
                $table->string('CREATED_BY', 20);
                $table->string('ASSIGNED_TO', 20);
                $table->string('DEPARTMENT_ID', 20);
                $table->timestamp('CLOSED_AT')->nullable();
                $table->string('CLOSED_BY', 20)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('EMPLOYEE_TASK_COMMENTS')) {
            Schema::create('EMPLOYEE_TASK_COMMENTS', function (Blueprint $table) {
                $table->integer('ID')->primary();
                $table->integer('TASK_ID')->index();
                $table->string('USER_ID', 20);
                $table->text('COMMENT');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('EMPLOYEE_TASK_ACTIVITIES')) {
            Schema::create('EMPLOYEE_TASK_ACTIVITIES', function (Blueprint $table) {
                $table->integer('ID')->primary();
                $table->integer('TASK_ID')->index();
                $table->string('ACTOR_ID', 20);
                $table->string('ACTION', 80);
                $table->string('FROM_STATUS', 30)->nullable();
                $table->string('TO_STATUS', 30)->nullable();
                $table->text('DESCRIPTION')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('EMPLOYEE_TASK_ACTIVITIES');
        Schema::dropIfExists('EMPLOYEE_TASK_COMMENTS');
        Schema::dropIfExists('EMPLOYEE_TASKS');
    }
};
