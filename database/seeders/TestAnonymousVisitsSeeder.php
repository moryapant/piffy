<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestAnonymousVisitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 anonymous visits for testing
        for ($i = 0; $i < 5; $i++) {
            DB::table('visits')->insert([
                'ip_address' => '127.0.0.'.($i + 1),
                'user_agent' => 'Test Browser/1.0',
                'page_visited' => 'http://localhost/test-page-'.$i,
                'page_title' => 'Test Page '.$i,
                'user_id' => null, // Anonymous user
                'activity_type' => 'page_view',
                'visited_at' => Carbon::now()->subMinutes($i * 5),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Create 3 authenticated visits for comparison
        for ($i = 0; $i < 3; $i++) {
            // Get a random user ID
            $userId = DB::table('users')->inRandomOrder()->value('id');

            if ($userId) {
                DB::table('visits')->insert([
                    'ip_address' => '192.168.1.'.($i + 1),
                    'user_agent' => 'Test Browser/1.0',
                    'page_visited' => 'http://localhost/user-test-page-'.$i,
                    'page_title' => 'User Test Page '.$i,
                    'user_id' => $userId,
                    'activity_type' => 'page_view',
                    'visited_at' => Carbon::now()->subMinutes($i * 3),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
