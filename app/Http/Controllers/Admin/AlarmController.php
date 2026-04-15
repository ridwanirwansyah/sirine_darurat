<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\AlarmLog;
use App\Models\AlarmState;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class AlarmController extends Controller
{
    // Cek status sirine
    public function status()
    {
        $state = AlarmState::firstOrCreate([], ['auto_off_duration' => 60]);

        if ($state->isExpired()) {
            $this->forceAutoOff($state);

            // Publish ALARM_OFF to MQTT to ensure relay is turned off
            app('mqtt')->publish(
                'projekiot/lampu/kendali',
                'ALARM_OFF',
                1
            );

            $state->refresh();
        }

        return response()->json([
            'is_on' => (bool) $state->is_on,
            'auto_off_at' => $state->auto_off_at,
            'auto_off_duration' => $state->auto_off_duration ?? 60,
        ]);
    }

    // fungsi untuk menyalakan atau mematikan sirine
    public function store(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:ALARM_ON,ALARM_OFF',
        ]);

        $state = AlarmState::firstOrCreate([], ['auto_off_duration' => 60]);

        if ($state->isExpired()) {
            $this->forceAutoOff($state);

            // Publish ALARM_OFF to MQTT to ensure relay is turned off
            app('mqtt')->publish(
                'projekiot/lampu/kendali',
                'ALARM_OFF',
                1
            );
        }

        if ($request->action === 'ALARM_ON') {
            $oldState = $state->is_on;

            $state->update([
                'is_on' => true,
                'activated_at' => now(),
                'auto_off_at' => now()->addSeconds($state->auto_off_duration),
            ]);

            AlarmLog::create([
                'user_id' => auth()->id(),
                'action'  => 'ALARM_ON',
                'event_time' => now(),
            ]);

            // ✅ LOG: Aktivasi sirine oleh admin
            AlarmLog::record([
                'action' => 'ALARM_ON',
                'target_type' => 'Alarm',
                'description' => "Admin " . (auth()->user()->name ?? 'Unknown') . " menyalakan sirine",
                'details' => [
                    'duration' => $state->auto_off_duration,
                    'auto_off_at' => $state->auto_off_at,
                    'activated_at' => $state->activated_at,
                    'previous_state' => $oldState,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            ]);
        }

        if ($request->action === 'ALARM_OFF') {
            $oldState = $state->is_on;

            $state->update([
                'is_on' => false,
                'activated_at' => null,
                'auto_off_at' => null,
            ]);

            AlarmLog::create([
                'user_id' => auth()->id(),
                'action'  => 'ALARM_OFF',
                'event_time' => now(),
            ]);

            // ✅ LOG: Pematian sirine oleh admin
            AlarmLog::record([
                'action' => 'ALARM_OFF',
                'target_type' => 'Alarm',
                'description' => "Admin " . (auth()->user()->name ?? 'Unknown') . " mematikan sirine",
                'details' => [
                    'previous_state' => $oldState,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'is_on'  => (bool) $state->is_on,
            'auto_off_at' => $state->auto_off_at,
            'auto_off_duration' => $state->auto_off_duration,
        ]);
    }

    /**
     * Update durasi auto-off
     */
    public function updateDuration(Request $request)
    {
        $request->validate([
            'duration' => 'required|integer|min:5|max:300',
        ]);

        $state = AlarmState::firstOrCreate([], ['auto_off_duration' => 60]);
        $oldDuration = $state->auto_off_duration;

        $state->update([
            'auto_off_duration' => $request->duration,
        ]);

        // ✅ LOG: Perubahan durasi auto-off oleh admin
        AlarmLog::record([
            'action' => 'UPDATE_AUTO_OFF_DURATION',
            'target_type' => 'Alarm',
            'description' => "Admin " . (auth()->user()->name ?? 'Unknown') . " mengubah durasi auto-off sirine dari {$oldDuration} detik menjadi {$request->duration} detik",
            'old_data' => ['duration' => $oldDuration],
            'new_data' => ['duration' => $request->duration],
            'details' => [
                'updated_by' => auth()->user()->name ?? 'Unknown',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]
        ]);

        return response()->json([
            'status' => 'ok',
            'auto_off_duration' => $state->auto_off_duration,
            'message' => 'Durasi auto-off berhasil disimpan',
        ]);
    }

    /**
     * fungsi untuk mendapatkan log aktivitas sirine terbaru (ALARM_ON dan AUTO_OFF) untuk ditampilkan di dashboard admin
     */
    public function getLogs()
    {
        $logs = AlarmLog::with('user')
            ->whereIn('action', ['ALARM_ON', 'AUTO_OFF'])  // Hanya ALARM_ON dan AUTO_OFF
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_name' => $log->user?->name ?? 'System',
                    'user_role' => $log->user?->role ?? 'AUTO',
                    'action' => $log->action,
                    'event_time' => $log->event_time,
                    'created_at' => $log->created_at,
                ];
            });

        return response()->json([
            'status' => 'ok',
            'logs' => $logs,
        ]);
    }

    /**
     * fungsi untuk melakukan pengecekan manual apakah sirine sudah expired (melebihi auto-off duration) dan mematikan sirine jika sudah expired, serta mencatat log aktivitas pengecekan manual ini oleh admin
      * fungsi ini bisa dipanggil dari dashboard admin untuk memastikan sirine mati jika sudah expired
     */
    public function checkAutoOff()
    {
        $state = AlarmState::first();

        if (!$state) {
            return response()->json([
                'status' => 'error',
                'message' => 'No alarm state found',
            ], 404);
        }

        if ($state->isExpired()) {
            $this->forceAutoOff($state);

            // Publish ALARM_OFF to MQTT
            try {
                app('mqtt')->publish(
                    'projekiot/lampu/kendali',
                    'ALARM_OFF',
                    1
                );
            } catch (\Exception $e) {
                Log::error('Error publishing MQTT: ' . $e->getMessage());
            }

            // ✅ LOG: Manual auto-off check oleh admin
            AlarmLog::record([
                'action' => 'MANUAL_AUTO_OFF_CHECK',
                'target_type' => 'Alarm',
                'description' => "Admin " . (auth()->user()->name ?? 'Unknown') . " melakukan pengecekan manual auto-off dan sirine mati karena expired",
                'details' => [
                    'triggered_by' => auth()->user()->name ?? 'System',
                    'ip_address' => request()->ip()
                ]
            ]);

            return response()->json([
                'status' => 'ok',
                'message' => 'Auto-off triggered successfully',
                'is_on' => false,
                'auto_off_at' => null,
            ]);
        }

        // Jika belum expired, kembalikan status saat ini
        return response()->json([
            'status' => 'ok',
            'message' => 'Alarm is still within the auto-off duration',
            'is_on' => $state->is_on,
            'auto_off_at' => $state->auto_off_at,
            'time_remaining' => $state->auto_off_at ? $state->auto_off_at->diffInSeconds(now()) : null,
        ]);
    }

    /**
     * fungsi untuk memaksa auto-off sirine, digunakan ketika sirine sudah expired tapi belum dimatikan, atau ketika admin melakukan pengecekan manual dan menemukan bahwa sirine sudah expired, maka fungsi ini akan mematikan sirine dan mencatat log aktivitas auto-off yang dipicu oleh sistem (bukan oleh admin) dengan detail yang lengkap
      * fungsi ini juga akan dipanggil oleh scheduler untuk memastikan sirine mati jika sudah expired, sehingga bisa menangani auto-off secara otomatis tanpa perlu menunggu admin melakukan pengecekan manual
     */
    private function forceAutoOff(AlarmState $state)
    {
        $oldState = $state->is_on;

        $state->update([
            'is_on' => false,
            'activated_at' => null,
            'auto_off_at' => null,
        ]);

        AlarmLog::create([
            'action' => 'AUTO_OFF',
            'event_time' => now(),
        ]);

        // ✅ LOG: Auto off sirine (dari sistem)
        AlarmLog::record([
            'action' => 'AUTO_OFF',
            'target_type' => 'Alarm',
            'description' => "Sirine dimatikan secara otomatis karena waktu habis (auto-off)",
            'details' => [
                'previous_state' => $oldState,
                'trigger' => 'auto_expiry',
                'timestamp' => now()->toISOString(),
                'auto_off_duration' => $state->auto_off_duration
            ]
        ]);
    }
}
