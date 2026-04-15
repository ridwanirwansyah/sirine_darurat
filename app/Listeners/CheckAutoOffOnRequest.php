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
            $state = AlarmState::first();

            if (!$state) {
                $state = AlarmState::create([
                    'is_on' => false,
                    'auto_off_duration' => 60
                ]);
            }

            if (!$state->is_on) {
                $this->info('Alarm is OFF, no action needed.');
                return;
            }

            if (!$state->auto_off_at) {
                $this->info('No auto-off time set, skipping.');
                return;
            }

            $this->info("Alarm is ON. Auto-off scheduled at: {$state->auto_off_at}");
            $this->info("Current time: " . now());

            if (now()->gte($state->auto_off_at)) {
                $this->info('Auto-off triggered! Turning off alarm...');
                
                // Simpan data sebelum update
                $sessionId = $state->current_session_id;
                $duration = $state->auto_off_duration;
                $wasOnFor = $state->activated_at ? now()->diffInSeconds($state->activated_at) : 0;
                
                // Update state
                $state->is_on = false;
                $state->activated_at = null;
                $state->auto_off_at = null;
                $state->save();
                
                // Log ke alarm_logs
                AlarmLog::create([
                    'action' => 'AUTO_OFF',
                    'session_id' => $sessionId,
                    'event_time' => now(),
                    'details' => json_encode([
                        'trigger' => 'scheduler',
                        'duration' => $duration,
                        'was_on_for' => $wasOnFor,
                        'message' => 'Sirine dimatikan otomatis oleh scheduler'
                    ])
                ]);
                
                // Kirim MQTT
                $this->publishMqttCommand('ALARM_OFF');
                
                $this->info('✓ Alarm turned off successfully');
            } else {
                $remaining = now()->diffInSeconds($state->auto_off_at);
                $this->info("Alarm will auto-off in {$remaining} seconds");
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('CheckAutoOffAlarm error: ' . $e->getMessage());
        }
    }
    
    private function publishMqttCommand(string $command): void
    {
        try {
            if (app()->has('mqtt')) {
                app('mqtt')->publish('projekiot/lampu/kendali', $command, 1);
                $this->info("✓ MQTT {$command} command published");
            } else {
                // Fallback: gunakan HTTP request ke broker MQTT
                $this->info("MQTT service not available, trying fallback...");
                $this->publishViaHttp($command);
            }
        } catch (\Exception $e) {
            $this->error("Error publishing MQTT command: " . $e->getMessage());
        }
    }
    
    private function publishViaHttp(string $command): void
    {
        // Fallback: simpan ke file atau database untuk dibaca ESP
        try {
            \Illuminate\Support\Facades\Cache::put('mqtt_command', $command, 10);
            $this->info("Command saved to cache: {$command}");
        } catch (\Exception $e) {
            $this->error("Fallback failed: " . $e->getMessage());
        }
    }
}