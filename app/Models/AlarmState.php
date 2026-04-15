<?php
// app/Models/AlarmState.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AlarmState extends Model
{
    protected $table = 'alarm_states';
    
    protected $fillable = [
        'is_on',
        'activated_at',
        'auto_off_at',
        'auto_off_duration',
        'current_session_id',
        'report_created',
        'activated_by'
    ];
    
    protected $casts = [
        'is_on' => 'boolean',
        'activated_at' => 'datetime',
        'auto_off_at' => 'datetime',
        'report_created' => 'boolean'
    ];
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        $state = self::first();
        if (!$state) {
            $state = self::create([
                'is_on' => false,
                'auto_off_duration' => 60
            ]);
        }
        return $state;
    }
    
    /**
     * Get duration in seconds
     */
    public function getDuration(): int
    {
        return $this->auto_off_duration ?? 60;
    }
    
    /**
     * Check if alarm is expired (should be turned off)
     */
    public function isExpired(): bool
    {
        if (!$this->is_on) {
            return false;
        }
        
        if (!$this->auto_off_at) {
            return false;
        }
        
        $expired = now()->gte($this->auto_off_at);
        
        if ($expired) {
            Log::info('Alarm expired check', [
                'is_on' => $this->is_on,
                'auto_off_at' => $this->auto_off_at,
                'now' => now(),
                'expired' => true
            ]);
        }
        
        return $expired;
    }
    
    /**
     * Get remaining seconds before auto-off
     */
    public function getRemainingSeconds(): int
    {
        if (!$this->is_on || !$this->auto_off_at) {
            return 0;
        }
        
        $remaining = now()->diffInSeconds($this->auto_off_at, false);
        
        return max(0, $remaining);
    }
    
    /**
     * Turn on alarm
     */
    public function turnOn(string $activatedBy, ?string $sessionId = null): bool
    {
        $duration = $this->getDuration();
        $sessionId = $sessionId ?? $this->generateSessionId();
        
        $this->update([
            'is_on' => true,
            'activated_at' => now(),
            'auto_off_at' => now()->addSeconds($duration),
            'current_session_id' => $sessionId,
            'report_created' => false,
            'activated_by' => $activatedBy
        ]);
        
        Log::info('Alarm turned ON', [
            'session_id' => $sessionId,
            'activated_by' => $activatedBy,
            'duration' => $duration,
            'auto_off_at' => $this->auto_off_at
        ]);
        
        return true;
    }
    
    /**
     * Turn off alarm
     */
    public function turnOff(string $triggerSource = 'manual'): bool
    {
        if (!$this->is_on) {
            return false;
        }
        
        $wasOnFor = $this->activated_at ? now()->diffInSeconds($this->activated_at) : 0;
        $duration = $this->getDuration();
        $sessionId = $this->current_session_id;
        $reportWasCreated = $this->report_created;
        
        $this->update([
            'is_on' => false,
            'activated_at' => null,
            'auto_off_at' => null,
        ]);
        
        // Log the turn off event
        AlarmLog::record([
            'action' => $triggerSource === 'auto_off' ? 'AUTO_OFF' : 'ALARM_OFF',
            'session_id' => $sessionId,
            'trigger_source' => $triggerSource,
            'target_type' => 'Alarm',
            'description' => $triggerSource === 'auto_off' 
                ? "Sirine dimatikan secara otomatis setelah {$duration} detik"
                : "Sirine dimatikan secara manual",
            'details' => [
                'was_on_for' => $wasOnFor,
                'duration' => $duration,
                'report_was_created' => $reportWasCreated,
                'activated_by' => $this->activated_by
            ]
        ]);
        
        Log::info('Alarm turned OFF', [
            'trigger_source' => $triggerSource,
            'session_id' => $sessionId,
            'was_on_for' => $wasOnFor,
            'duration' => $duration
        ]);
        
        return true;
    }
    
    /**
     * Auto-off when expired (called by scheduler)
     */
    public function autoOff(): bool
    {
        if (!$this->is_on) {
            Log::info('Auto-off skipped: Alarm already off');
            return false;
        }
        
        if (!$this->isExpired()) {
            Log::info('Auto-off skipped: Alarm not expired yet', [
                'remaining' => $this->getRemainingSeconds()
            ]);
            return false;
        }
        
        Log::info('Auto-off triggered by scheduler');
        return $this->turnOff('auto_off');
    }
    
    /**
     * Mark report as created for current session
     */
    public function markReportCreated(): bool
    {
        if (!$this->current_session_id) {
            return false;
        }
        
        $this->update([
            'report_created' => true
        ]);
        
        Log::info('Report marked as created', [
            'session_id' => $this->current_session_id
        ]);
        
        return true;
    }
    
    /**
     * Check if report has been created for current session
     */
    public function hasReportForCurrentSession(): bool
    {
        return $this->report_created;
    }
    
    /**
     * Generate unique session ID
     */
    private function generateSessionId(): string
    {
        return uniqid('alarm_', true) . '_' . bin2hex(random_bytes(8));
    }
}