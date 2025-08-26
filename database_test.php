<?php

// Simple script to test database connection and table structure
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing database connection...\n";

try {
    // Test database connection
    if (DB::connection()->getPdo()) {
        echo 'Database connected successfully: '.DB::connection()->getDatabaseName()."\n";
    }

    // Check visits table structure
    $columns = Schema::getColumnListing('visits');
    echo "Visits table columns: \n";
    print_r($columns);

    // Count records in visits table
    $visitsCount = DB::table('visits')->count();
    echo "Visits count: $visitsCount\n";

    // Insert a test record
    $result = DB::table('visits')->insert([
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Script',
        'page_visited' => 'http://test-script.local',
        'page_title' => 'Database Test Script',
        'visited_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo 'Test record insert result: '.($result ? 'Success' : 'Failed')."\n";

    // Count again to verify insertion
    $newCount = DB::table('visits')->count();
    echo "New visits count: $newCount\n";

    // Get the last 5 records
    $recentVisits = DB::table('visits')->orderBy('id', 'desc')->limit(5)->get();
    echo "Recent visits: \n";
    print_r($recentVisits);

} catch (\Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
