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
        Schema::create('exit_interviews', function (Blueprint $table) {
            $table->id();
            
            // Link to the existing User table (Employee)
            // Assumes your users table id is unsignedBigInteger
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); 

            // Separation Details [cite: 2]
            $table->string('reporting_officer'); // Name of Reporting Officer
            $table->string('separation_type');   // Resignation, Retirement, etc.

            // Reasons for Leaving (Checkboxes) [cite: 3]
            // We store the array of selected reasons (e.g., ["Better Pay", "Health Reasons"]) as JSON
            $table->json('reasons')->nullable();

            // Open Ended Questions [cite: 3, 4, 12]
            $table->text('prevented_departure')->nullable(); // "What circumstances would have prevented..."
            $table->text('liked_most')->nullable();
            $table->text('liked_least')->nullable();
            $table->text('suggestions')->nullable(); // "What suggestions would you give..."

            // Scales [cite: 5]
            $table->string('workload'); // "Too Heavy", "About Right", "Too Light"
            $table->string('recommend_friend'); // "Definitely", "With Reservations", "Never"

            // Matrix Ratings (Stored as JSON)
            // Stores the RO ratings: { "Was consistently fair": "Always", ... } [cite: 6, 7]
            $table->json('ro_ratings'); 
            
            // Stores Company ratings: { "Co-operation within your department": "Good", ... } [cite: 8, 9, 10, 11]
            $table->json('company_ratings');

            // Permission [cite: 13]
            $table->boolean('share_with_ro')->default(false); // "Would you recommend us to show this form..."

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exit_interviews');
    }
};
