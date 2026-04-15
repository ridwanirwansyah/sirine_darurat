<?php
// app/Console/Commands/CheckAutoOffAlarm.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AlarmState;
use App\Models\AlarmLog;
use Illuminate\Support\Facades\Log;

class CheckAutoOffAlarm extends Command
{
    protected $signature = 'alarm:check-auto-off';
    protected $description = 'Check and automatically turn off alarm if auto-off time has been reached';
    
    public function handle(): void
    {
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Checking for auto-off alarms...');
        
        try {
            $state = AlarmState::getInstance();
            
            if (!$state->is_on) {
                $this->info('Alarm is OFF, no action needed.');
                return;
            }
            
            if (!$state->auto_off_at) {
                $this->info('No auto-off time set, skipping.');
                return;
            }
            
            $remaining = $state->getRemainingSeconds();
            $this->info("Alarm is ON. Auto-off scheduled at: {$state->auto_off_at}");
            $this->info("Remaining seconds: {$remaining}");
            
            if ($state->isExpired()) {
                $this->info('Auto-off triggered! Turning off alarm...');
                
                // fungsi untuk mematikan alarm dan mengembalikan hasilnya
                $autoOffResult = $state->autoOff();
                
                if ($autoOffResult) {
                    // Publish MQTT command
                    $this->publishMqttCommand('ALARM_OFF');
                    
                    $this->info('✓ Alarm turned off successfully');
                    $this->info('Auto-off at: ' . now());
                } else {
                    $this->error('Failed to auto-off alarm');
                }
            } else {
                $this->info("Alarm will auto-off in {$remaining} seconds");
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('CheckAutoOffAlarm error: ' . $e->getMessage());
        }
    }
    
    /**
     * fungsi untuk publish MQTT command, dengan logging ke database dan error handling 
     */
    private function publishMqttCommand(string $command): void
    {
        try {
            if (app()->has('mqtt')) {
                app('mqtt')->publish('projekiot/lampu/kendali', $command, 1);
                $this->info("✓ MQTT {$command} command published");
                
                // Log to database
                AlarmLog::record([
                    'action' => 'MQTT_PUBLISH',
                    'trigger_source' => 'scheduler',
                    'target_type' => 'MQTT',
                    'description' => "MQTT command published: {$command}",
                    'details' => ['command' => $command]
                ]);
            } else {
                $this->warn("MQTT service not available, command not published");
                Log::warning("MQTT service not available for command: {$command}");
            }
        } catch (\Exception $e) {
            $this->error("Error publishing MQTT command: " . $e->getMessage());
            Log::error("MQTT publish error: " . $e->getMessage());
        }
    }
}