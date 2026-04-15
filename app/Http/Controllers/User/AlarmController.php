<?php
// app/Http/Controllers/User/AlarmController.php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\AlarmLog;
use App\Models\AlarmState;
use App\Models\Incident;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class AlarmController extends Controller
{
    /**
     * fungsi untuk mendapatkan status sirine ssat ini, termasuk apakah sirine sedang menyala atau tidak, kapan waktu auto-off, durasi auto-off, dan informasi terkait lainnya yang diperlukan untuk menampilkan status sirine di halaman utama user, serta melakukan pengecekan auto-off secara redundan untuk memastikan sirine mati jika sudah melewati waktu auto-off meskipun scheduler gagal      
     * GET /user/alarm/current-state
     */
    public function getCurrentState()
    {
        try {
            $state = AlarmState::getInstance();

            // Redundant check untuk auto-off jika sudah melewati waktu auto-off
            if ($state->is_on && $state->isExpired()) {
                $state->autoOff();
                $this->publishMqtt('ALARM_OFF');
                $state->refresh();
            }

            // Cek apakah user memiliki laporan aktif
            return response()->json([
                'success' => true,
                'is_on' => (bool) $state->is_on,
                'auto_off_duration' => $state->getDuration(),
                'auto_off_at' => $state->auto_off_at,
                'activated_at' => $state->activated_at,
                'remaining_seconds' => $state->getRemainingSeconds(),
                'session_id' => $state->current_session_id,
                'report_created' => $state->report_created
            ]);
        } catch (\Exception $e) {
            Log::error('getCurrentState error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'is_on' => false,
                'auto_off_duration' => 60,
                'remaining_seconds' => 0
            ]);
        }
    }


    /**
     * fungsi untuk menyalakan sirine, dengan pengecekan apakah user memiliki laporan aktif yang masih berjalan, dan jika tidak ada laporan aktif maka sirine akan dinyalakan, serta mencatat log aktivitas penyalakan sirine dengan detail yang lengkap, termasuk durasi auto-off, waktu auto-off, dan informasi terkait lainnya yang diperlukan untuk keperluan audit dan troubleshooting
     * POST /user/alarm/on
     */
    public function turnOn(Request $request)
    {
        try {
            $user = auth()->user();
            $state = AlarmState::getInstance();

            // Check if already on
            if ($state->is_on) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sirine sudah dalam keadaan menyala'
                ], 400);
            }

            // Cek apakah user memiliki laporan aktif
            $hasActiveIncident = Incident::where('user_id', $user->id)
                ->where('status', 'ACTIVE')
                ->exists();

            if ($hasActiveIncident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menyalakan sirine. Selesaikan laporan aktif Anda terlebih dahulu.'
                ], 403);
            }

            // Generate session ID untuk log sirine ini
            $sessionId = uniqid('alarm_', true);

            // Turn on alarm
            $state->turnOn($user->name, $sessionId);

            // Publish MQTT command
            $this->publishMqtt('ALARM_ON');

            // Log to alarm_logs
            AlarmLog::record([
                'action'         => 'ALARM_ON',
                'user_id'        => $user->id,
                'session_id'     => $sessionId,
                'trigger_source' => 'manual',
                'target_type'    => 'Alarm',
                'description'    => "User {$user->name} menyalakan sirine",
                'details'        => [
                    'duration'   => $state->getDuration(),
                    'auto_off_at' => $state->auto_off_at,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sirine berhasil dinyalakan',
                'is_on' => true,
                'session_id' => $sessionId,
                'auto_off_at' => $state->auto_off_at,
                'duration' => $state->getDuration(),
                'remaining_seconds' => $state->getRemainingSeconds()
            ]);
        } catch (\Exception $e) {
            Log::error('turnOn error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyalakan sirine: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * fungsi untuk mematikan sirine, dengan pengecekan apakah sirine sedang menyala atau tidak, dan jika sirine sedang menyala maka sirine akan dimatikan, serta mencatat log aktivitas pematian sirine dengan detail yang lengkap, termasuk informasi tentang siapa yang mematikan sirine (jika tersedia), durasi sirine menyala sebelum dimatikan, dan informasi terkait lainnya yang diperlukan untuk keperluan audit dan troubleshooting
     * POST /user/alarm/off
     */
    public function turnOff(Request $request)
    {
        try {
            $state = AlarmState::getInstance();
            $trigger = $request->input('trigger', 'manual');

            if (!$state->is_on) {
                return response()->json([
                    'success' => true,
                    'is_on' => false,
                    'message' => 'Sirine sudah dalam keadaan mati'
                ]);
            }

            // Turn off
            $state->turnOff($trigger);

            // Publish MQTT
            $this->publishMqtt('ALARM_OFF');

            return response()->json([
                'success' => true,
                'is_on' => false,
                'message' => 'Sirine berhasil dimatikan',
                'triggered_by' => $trigger
            ]);
        } catch (\Exception $e) {
            Log::error('turnOff error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mematikan sirine: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * fungsi untuk menyalakan sirine, dengan pengecekan apakah user memiliki laporan aktif yang masih berjalan, dan jika tidak ada laporan aktif maka sirine akan dinyalakan, serta mencatat log aktivitas penyalakan sirine dengan detail yang lengkap, termasuk durasi auto-off, waktu auto-off, dan informasi terkait lainnya yang diperlukan untuk keperluan audit dan troubleshooting
     * POST /user/alarm/mark-report-created
     */
    public function markReportCreated(Request $request)
    {
        try {
            $request->validate([
                'session_id' => 'required|string'
            ]);

            $state = AlarmState::getInstance();

            // Verify session ID matches
            if ($state->current_session_id !== $request->session_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session ID mismatch'
                ], 400);
            }

            $state->markReportCreated();

            // Also store in alarm_logs
            AlarmLog::record([
                'action' => 'REPORT_CREATED',
                'session_id' => $state->current_session_id,
                'target_type' => 'Alarm',
                'description' => 'Laporan kejadian telah dibuat untuk sesi sirine ini',
                'details' => [
                    'marked_by' => auth()->user()->name
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report marked as created'
            ]);
        } catch (\Exception $e) {
            Log::error('markReportCreated error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * fungsi untuk mendapatkan riwayat aktifitas sirine oleh user login
     * GET /user/alarm/history
     */
    public function getHistory(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $userId = auth()->id(); // ← tambahkan ini

            $logs = AlarmLog::with('user')
                ->where('user_id', $userId) // ← filter by user yang login
                ->whereIn('action', ['ALARM_ON', 'ALARM_OFF', 'AUTO_OFF']) // ← opsional: hanya ambil action relevan
                ->orderBy('event_time', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($log) {
                    return [
                        'id'          => $log->id,
                        'action'      => $log->action,
                        'user_name'   => $log->user ? $log->user->name : 'System',
                        'created_at'  => $log->event_time,
                        'session_id'  => $log->session_id,
                        'description' => $log->description
                    ];
                });

            return response()->json([
                'success' => true,
                'data'    => $logs
            ]);
        } catch (\Exception $e) {
            Log::error('getHistory error: ' . $e->getMessage());
            return response()->json(['success' => false, 'data' => []]);
        }
    }

    /**
     * fungsi memperbarui durasi mati otomastis sirine
     * POST /user/alarm/update-duration
     */
    public function updateDuration(Request $request)
    {
        try {
            $request->validate([
                'duration' => 'required|integer|min:10|max:300'
            ]);

            $state = AlarmState::getInstance();
            $oldDuration = $state->getDuration();

            $state->update([
                'auto_off_duration' => $request->duration
            ]);

            // If alarm is on, update auto_off_at
            if ($state->is_on && $state->activated_at) {
                $state->update([
                    'auto_off_at' => now()->addSeconds($request->duration)
                ]);
            }

            AlarmLog::record([
                'action' => 'UPDATE_DURATION',
                'target_type' => 'Alarm',
                'description' => "Durasi auto-off diubah dari {$oldDuration} detik menjadi {$request->duration} detik",
                'old_data' => ['duration' => $oldDuration],
                'new_data' => ['duration' => $request->duration]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Durasi auto-off diperbarui',
                'auto_off_duration' => $request->duration
            ]);
        } catch (\Exception $e) {
            Log::error('updateDuration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui durasi'
            ], 500);
        }
    }

    /**
     * fungsi untuk mendapatkan laporan aktif user
     * GET /user/api/incidents/active
     */
    public function getActiveIncidents()
    {
        try {
            $incidents = Incident::where('user_id', auth()->id())
                ->where('status', 'ACTIVE')
                ->orderBy('reported_at', 'desc')
                ->get();

            return response()->json([
                'count' => $incidents->count(),
                'incidents' => $incidents->map(function ($inc) {
                    return [
                        'id' => $inc->id,
                        'type' => $inc->type,
                        'status' => $inc->status,
                        'reported_at' => $inc->reported_at,
                        'reported_at_formatted' => $inc->reported_at ? $inc->reported_at->diffForHumans() : null
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('getActiveIncidents error: ' . $e->getMessage());
            return response()->json([
                'count' => 0,
                'incidents' => []
            ]);
        }
    }

    /**
     * Publish MQTT command
     */
    private function publishMqtt(string $command): void
    {
        try {
            if (app()->has('mqtt')) {
                app('mqtt')->publish('projekiot/lampu/kendali', $command, 1);
                Log::info("MQTT published: {$command}");
            } else {
                Log::warning("MQTT service not available");
            }
        } catch (\Exception $e) {
            Log::error("MQTT publish error: " . $e->getMessage());
        }
    }
}
