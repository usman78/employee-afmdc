<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notice_board', function (Blueprint $table) {
            $table->dateTime('publish_starts_at')->nullable()->after('is_published');
            $table->dateTime('publish_ends_at')->nullable()->after('publish_starts_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notice_board', function (Blueprint $table) {
            $table->dropColumn(['publish_starts_at', 'publish_ends_at']);
        });
    }
};
