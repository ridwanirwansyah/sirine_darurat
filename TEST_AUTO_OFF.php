<?php
/**
 * Test Auto-Off Functionality
 * Run this to verify auto-off is working
 */

// Bootstrap Laravel
require_once 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Get AlarmState model
$AlarmState = app(App\Models\AlarmState::class);

echo "=== AUTO-OFF TEST ===\n\n";

// Get current state
$state = $AlarmState::first();

if (!$state) {
    echo "No alarm state found. Creating default...\n";
    $state = $AlarmState::create([
        'is_on' => false,
        'auto_off_duration' => 5
    ]);
}

echo "Current State:\n";
echo "  is_on: " . ($state->is_on ? 'YES' : 'NO') . "\n";
echo "  auto_off_at: " . ($state->auto_off_at ? $state->auto_off_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
echo "  auto_off_duration: " . $state->auto_off_duration . " seconds\n";
echo "\n";

// Turn on alarm with 5 second auto-off
echo "Turning alarm ON (5 second auto-off)...\n";
$state->update([
    'is_on' => true,
    'activated_at' => now(),
    'auto_off_at' => now()->addSeconds(5)
]);

echo "  is_on: " . ($state->is_on ? 'YES' : 'NO') . "\n";
echo "  auto_off_at: " . $state->auto_off_at->format('Y-m-d H:i:s') . "\n";
echo "  remaining: " . $state->getRemainingSeconds() . " seconds\n\n";

// Wait 6 seconds
echo "Waiting 6 seconds for auto-off...\n";
sleep(6);

// Check remaining time
$state->refresh();
echo "After wait:\n";
echo "  remaining: " . $state->getRemainingSeconds() . " seconds\n";
echo "  isExpired: " . ($state->isExpired() ? 'YES' : 'NO') . "\n\n";

// Trigger auto-off
echo "Triggering autoOff()...\n";
$result = $state->autoOff();
echo "  Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n\n";

// Check final state
$state->refresh();
echo "Final State:\n";
echo "  is_on: " . ($state->is_on ? 'YES' : 'NO') . "\n";
echo "  auto_off_at: " . ($state->auto_off_at ? $state->auto_off_at->format('Y-m-d H:i:s') : 'NULL') . "\n\n";

// Check logs
echo "Recent AlarmLogs:\n";
$logs = \App\Models\AlarmLog::orderBy('created_at', 'desc')->take(3)->get();
foreach ($logs as $log) {
    echo "  [{$log->action}] {$log->user_name} - {$log->description}\n";
}

echo "\n=== TEST COMPLETE ===\n";
