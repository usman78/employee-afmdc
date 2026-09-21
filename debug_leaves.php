<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Leave;
use Carbon\Carbon;

echo "=== DEBUG INFO ===\n";

// Check status distribution
$statuses = Leave::selectRaw('status, COUNT(*) as count')->groupBy('status')->get();
echo "Leaves by status:\n";
foreach($statuses as $s) {
    echo "  Status " . $s->status . ": " . $s->count . "\n";
}

// Get min and max dates
echo "\nDate range in database:\n";
$minDate = Leave::orderBy('from_date')->first();
$maxDate = Leave::orderByDesc('from_date')->first();
echo "Earliest from_date: " . $minDate->from_date . "\n";
echo "Latest from_date: " . $maxDate->from_date . "\n";

// Test with status 3, 5, 9 (which have data)
echo "\n\n=== Testing status 3, 5, 9 with May 2026 ===\n";
$may2026Start = Carbon::createFromFormat('Y-m', '2026-05')->startOfMonth();
$may2026End = Carbon::createFromFormat('Y-m', '2026-05')->endOfMonth();

echo "Testing May 2026: " . $may2026Start->toDateString() . " to " . $may2026End->toDateString() . "\n\n";

// Test each status
foreach([1, 3, 5, 7, 9] as $status) {
    $count = Leave::where('status', $status)
        ->whereRaw("(TRUNC(from_date) <= TRUNC(TO_DATE(?, 'YYYY-MM-DD')) AND TRUNC(to_date) >= TRUNC(TO_DATE(?, 'YYYY-MM-DD')))", 
            [$may2026End->toDateString(), $may2026Start->toDateString()])
        ->count();
    echo "Status " . $status . " (May 2026): " . $count . " leaves\n";
}



