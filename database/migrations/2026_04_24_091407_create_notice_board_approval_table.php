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
        Schema::create('notice_board_approval', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notice_id');
            $table->string('approver_id', 50);
            $table->string('approval_status')->default('pending'); // pending, approved, rejected
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->foreign('notice_id')->references('id')->on('notice_board')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notice_board_approval');
    }
};
