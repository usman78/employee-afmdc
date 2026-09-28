<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('PASSWORD_CHANGE_TRAILS')) {
            Schema::create('PASSWORD_CHANGE_TRAILS', function (Blueprint $table) {
                $table->id('ID');
                $table->string('EMP_CODE', 20)->index();
                $table->string('CHANGED_BY', 20);
                $table->string('CHANGE_REASON', 50);
                $table->string('IP_ADDRESS', 45)->nullable();
                $table->string('USER_AGENT', 1000)->nullable();
                $table->timestamp('CHANGED_AT')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('PASSWORD_CHANGE_TRAILS');
    }
};
