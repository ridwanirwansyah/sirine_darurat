<?php
// app/Http/Middleware/CheckAutoOff.php

namespace App\Http\Middleware;

use Closure;
use App\Models\AlarmState;
use Illuminate\Support\Facades\Log;

class CheckAutoOff
{
    /**
     * Handle an incoming request.
     * Middleware ini akan mengecek auto-off setiap ada request
     * Note: Designed to handle auto-off gracefully even without authenticated user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $state = AlarmState::first();
            
            if ($state && $state->is_on && $state->isExpired()) {
                // Auto-off triggered
                Log::info('Auto-off condition detected via middleware', [
                    'request_url' => $request->fullUrl(),
                    'auto_off_at' => $state->auto_off_at,
                    'current_time' => now()
                ]);
                
                $autoOffResult = $state->autoOff();
                
                if ($autoOffResult) {
                    // Kirim MQTT
                    try {
                        if (app()->has('mqtt')) {
                            app('mqtt')->publish('projekiot/lampu/kendali', 'ALARM_OFF', 1);
                            Log::info('MQTT ALARM_OFF sent from middleware');
                        }
                    } catch (\Exception $e) {
                        Log::warning('MQTT publish failed in middleware: ' . $e->getMessage());
                        // Continue anyway - state is already changed
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('CheckAutoOff middleware error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // Don't block the request - continue anyway
        }
        
        return $next($request);
    }
}