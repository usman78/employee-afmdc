<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExitInterviewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'id' => Str::random(10),
            'user_id' => Str::random(10),
            'reporting_officer' => Str::random(10),
            'separation_type' => Str::random(10),
            'workload' => Str::random(10),
            'recommend_friend' => Str::random(10),
            'ro_ratings' => json_encode([]),
            'company_ratings' => json_encode([]),
            'share_with_ro' => false,
        ]);
    }
}
