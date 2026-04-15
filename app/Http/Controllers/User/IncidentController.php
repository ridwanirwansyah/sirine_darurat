<?php

namespace App\Http\Controllers\User;

use App\Models\Incident;
use App\Models\User;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\AlarmLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class IncidentController extends Controller
{
    /**
     * Tampilkan halaman daftar insiden
     */
    public function index()
    {
        $incidents = Incident::where('user_id', auth()->id())
            ->orderBy('reported_at', 'desc')
            ->paginate(15);

        $activeIncidents = Incident::where('user_id', auth()->id())
            ->where('status', 'ACTIVE')
            ->count();

        $resolvedIncidents = Incident::where('user_id', auth()->id())
            ->where('status', 'RESOLVED')
            ->count();

        $falseAlarmIncidents = Incident::where('user_id', auth()->id())
            ->where('status', 'FALSE_ALARM')
            ->count();

        return view('user.incidents.index', compact(
            'incidents',
            'activeIncidents',
            'resolvedIncidents',
            'falseAlarmIncidents'
        ));
    }

    /**
     * Tampilkan form buat laporan
     */
    public function create()
    {
        $incidentTypes = [
            'KEBAKARAN'          => 'Kebakaran 🔥',
            'PENCURIAN'          => 'Pencurian 🚨',
            'GEMPA_BUMI'         => 'Gempa Bumi 📍',
            'BANJIR'             => 'Banjir 🌊',
            'KECELAKAAN'         => 'Kecelakaan 🚗',
            'PENYERANGAN'        => 'Penyerangan ⚠️',
            'GANGGUAN_KEAMANAN'  => 'Gangguan Keamanan 🛡️',
            'LAINNYA'            => 'Lainnya ❓',
        ];

        return view('user.incidents.create', compact('incidentTypes'));
    }

    /**
     * Simpan laporan insiden baru dengan gambar
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'      => 'required|in:KEBAKARAN,PENCURIAN,GEMPA_BUMI,BANJIR,KECELAKAAN,PENYERANGAN,GANGGUAN_KEAMANAN,LAINNYA',
            'description' => 'required|string|min:10|max:500',
            'location'  => 'nullable|string|max:100',
            'images'    => 'nullable|array|max:3',
            'images.*'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'images.max'      => 'Maksimal 3 gambar yang dapat diupload',
            'images.*.image'  => 'File harus berupa gambar',
            'images.*.mimes'  => 'Gambar harus berformat JPEG, PNG, atau JPG',
            'images.*.max'    => 'Ukuran gambar maksimal 2MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $user = auth()->user();

            $incident = Incident::create([
                'user_id'     => $user->id,
                'type'        => $request->type,
                'description' => $request->description,
                'location'    => $request->location ?? null,
                'status'      => 'ACTIVE',
                'reported_at' => now(),
            ]);

            // Handle upload gambar
            $storedImages = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('incidents', $fileName, 'public');

                    if ($path) {
                        $storedImages[] = $fileName;
                    }
                }

                if (!empty($storedImages)) {
                    $incident->images = $storedImages;
                    $incident->save();
                }
            }

            // ✅ LOG: Catat aktivitas membuat laporan insiden
            $typeLabels = [
                'KEBAKARAN' => 'Kebakaran',
                'PENCURIAN' => 'Pencurian',
                'GEMPA_BUMI' => 'Gempa Bumi',
                'BANJIR' => 'Banjir',
                'KECELAKAAN' => 'Kecelakaan',
                'PENYERANGAN' => 'Penyerangan',
                'GANGGUAN_KEAMANAN' => 'Gangguan Keamanan',
                'LAINNYA' => 'Lainnya',
            ];

            AlarmLog::record([
                'action' => 'CREATE_INCIDENT',
                'target_type' => 'Incident',
                'target_id' => $incident->id,
                'description' => "User {$user->name} membuat laporan kejadian baru: " . ($typeLabels[$incident->type] ?? $incident->type),
                'new_data' => $incident->toArray(),
                'details' => [
                    'incident_type' => $incident->type,
                    'location' => $incident->location,
                    'has_images' => !empty($storedImages),
                    'image_count' => count($storedImages),
                    'description_length' => strlen($incident->description),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            ]);

            // Kirim notifikasi ke admin
            try {
                $admins = User::where('role', 'admin')->get();

                foreach ($admins as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type'    => 'NEW_INCIDENT',
                        'title'   => 'Insiden Baru Dilaporkan',
                        'message' => "Insiden {$incident->type} dilaporkan oleh " . $user->name,
                        'data'    => json_encode([
                            'incident_id'   => $incident->id,
                            'type'          => $incident->type,
                            'reporter_name' => $user->name,
                            'reported_at'   => $incident->reported_at->format('d/m/Y H:i:s')
                        ]),
                        'read_at' => null
                    ]);
                }

                Log::info('Notification sent to admins for new incident', [
                    'incident_id' => $incident->id,
                    'type'        => $incident->type,
                    'admin_count' => $admins->count()
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send notification: ' . $e->getMessage());
            }

            return response()->json([
                'status'      => 'success',
                'message'     => 'Laporan insiden berhasil dibuat. Sirine dapat diaktifkan sekarang.',
                'incident_id' => $incident->id,
                'has_images'  => !empty($storedImages),
                'image_count' => count($storedImages),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating incident: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat laporan insiden. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Tandai insiden sebagai terselesaikan - HANYA ADMIN
     */
    public function resolve($id, Request $request)
    {
        $incident = Incident::findOrFail($id);

        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya admin yang dapat menandai insiden sebagai selesai'
            ], 403);
        }

        $oldStatus = $incident->status;

        $incident->update([
            'status'           => 'RESOLVED',
            'resolved_at'      => now(),
            'resolved_by'      => auth()->id(),
            'resolution_notes' => $request->input('notes', null),
        ]);

        // ✅ LOG: Catat aktivitas resolve incident oleh admin
        AlarmLog::record([
            'action' => 'RESOLVE_INCIDENT',
            'target_type' => 'Incident',
            'target_id' => $incident->id,
            'description' => "Admin " . auth()->user()->name . " menandai laporan #{$incident->id} sebagai selesai",
            'old_data' => ['status' => $oldStatus],
            'new_data' => ['status' => 'RESOLVED', 'resolved_at' => now()],
            'details' => [
                'incident_type' => $incident->type,
                'resolution_notes' => $request->input('notes'),
                'ip_address' => $request->ip()
            ]
        ]);

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'INCIDENT_RESOLVED',
            'details'    => json_encode([
                'incident_id' => $incident->id,
                'type'        => $incident->type,
                'resolved_at' => now()->toDateTimeString()
            ]),
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Insiden telah ditandai sebagai terselesaikan.',
        ]);
    }

    /**
     * Tandai insiden sebagai alarm palsu - HANYA ADMIN
     */
    public function falseAlarm($id, Request $request)
    {
        $incident = Incident::findOrFail($id);

        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya admin yang dapat menandai insiden sebagai alarm palsu'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $oldStatus = $incident->status;

        $incident->update([
            'status'             => 'FALSE_ALARM',
            'resolved_at'        => now(),
            'resolved_by'        => auth()->id(),
            'false_alarm_reason' => $request->input('reason', 'Tidak disebutkan'),
        ]);

        // ✅ LOG: Catat aktivitas false alarm oleh admin
        AlarmLog::record([
            'action' => 'FALSE_ALARM_INCIDENT',
            'target_type' => 'Incident',
            'target_id' => $incident->id,
            'description' => "Admin " . auth()->user()->name . " menandai laporan #{$incident->id} sebagai False Alarm",
            'old_data' => ['status' => $oldStatus],
            'new_data' => ['status' => 'FALSE_ALARM'],
            'details' => [
                'incident_type' => $incident->type,
                'reason' => $request->input('reason', 'Tidak disebutkan'),
                'ip_address' => $request->ip()
            ]
        ]);

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'INCIDENT_FALSE_ALARM',
            'details'    => json_encode([
                'incident_id' => $incident->id,
                'type'        => $incident->type,
                'reason'      => $request->input('reason', 'Tidak disebutkan')
            ]),
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Insiden ditandai sebagai alarm palsu.',
        ]);
    }

    /**
     * Hapus insiden dan gambar-gambar terkait
     */
    public function destroy($id)
    {
        $incident = Incident::findOrFail($id);

        if ($incident->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $incidentData = $incident->toArray();
        $userName = auth()->user()->name;

        // Hapus gambar dari storage
        if ($incident->images && is_array($incident->images)) {
            foreach ($incident->images as $image) {
                $filePath = 'incidents/' . $image;
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        }

        $incident->delete();

        // ✅ LOG: Catat aktivitas hapus incident
        AlarmLog::record([
            'action' => 'DELETE_INCIDENT',
            'target_type' => 'Incident',
            'target_id' => $incident->id,
            'description' => ($incident->user_id === auth()->id() ? "User {$userName}" : "Admin {$userName}") . " menghapus laporan kejadian #{$incident->id}",
            'old_data' => $incidentData,
            'details' => [
                'incident_type' => $incidentData['type'],
                'had_images' => !empty($incidentData['images']),
                'deleted_by_role' => auth()->user()->role
            ]
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Laporan insiden telah dihapus.',
        ]);
    }

    /**
     * Hapus gambar spesifik dari insiden
     */
    public function deleteImage($incidentId, $imageName, Request $request)
    {
        $incident = Incident::findOrFail($incidentId);

        if ($incident->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $filePath = 'incidents/' . $imageName;
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        $images = $incident->images ?? [];
        if (($key = array_search($imageName, $images)) !== false) {
            unset($images[$key]);
            $incident->images = array_values($images);
            $incident->save();
        }

        // ✅ LOG: Catat aktivitas hapus gambar incident
        AlarmLog::record([
            'action' => 'DELETE_INCIDENT_IMAGE',
            'target_type' => 'Incident',
            'target_id' => $incident->id,
            'description' => (auth()->user()->name) . " menghapus gambar dari laporan kejadian #{$incident->id}",
            'details' => [
                'deleted_image' => $imageName,
                'remaining_images' => count($incident->images)
            ]
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Gambar berhasil dihapus.',
        ]);
    }

    /**
     * Get active incidents (AJAX)
     */
    public function getActive()
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                throw new \Exception('User not authenticated');
            }

            $incidents = Incident::where('user_id', $userId)
                ->where('status', 'ACTIVE')
                ->orderBy('reported_at', 'desc')
                ->get();

            return response()->json([
                'count'     => $incidents->count(),
                'incidents' => $incidents->map(function ($inc) {
                    return [
                        'id'                  => $inc->id,
                        'type'                => $this->getTypeLabel($inc->type),
                        'type_code'           => $inc->type,
                        'description'         => $inc->description,
                        'location'            => $inc->location,
                        'images'              => $inc->images ?? [],
                        'image_urls'          => $this->getImageUrls($inc->images ?? []),
                        'status'              => $inc->status,
                        'alarm_session_id'    => $inc->alarm_session_id, // jika ada kolom ini
                        'reported_at'         => $inc->reported_at ? $inc->reported_at->toDateTimeString() : null,
                        'reported_at_formatted' => $inc->reported_at ? $inc->reported_at->diffForHumans() : null,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getActive: ' . $e->getMessage());
            return response()->json([
                'count'     => 0,
                'incidents' => [],
                'error'     => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Get type label
     */
    private function getTypeLabel($type)
    {
        $labels = [
            'KEBAKARAN'         => 'Kebakaran',
            'PENCURIAN'         => 'Pencurian',
            'GEMPA_BUMI'        => 'Gempa Bumi',
            'BANJIR'            => 'Banjir',
            'KECELAKAAN'        => 'Kecelakaan',
            'PENYERANGAN'       => 'Penyerangan',
            'GANGGUAN_KEAMANAN' => 'Gangguan Keamanan',
            'LAINNYA'           => 'Lainnya',
        ];

        return $labels[$type] ?? $type;
    }

    /**
     * Get image URLs
     */
    private function getImageUrls($images)
    {
        if (empty($images) || !is_array($images)) {
            return [];
        }

        return array_map(function ($image) {
            return asset('storage/incidents/' . $image);
        }, $images);
    }

    /**
     * Cek apakah user punya insiden aktif untuk mengaktifkan sirine
     */
    public function canActivateAlarm()
    {
        $userId = auth()->id();

        Log::info('Checking can activate alarm for user:', ['user_id' => $userId]);

        $activeIncidents = Incident::where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->get();

        $hasActiveIncident = $activeIncidents->count() > 0;

        Log::info('Active incidents found:', [
            'user_id'   => $userId,
            'count'     => $activeIncidents->count(),
            'incidents' => $activeIncidents->pluck('id')
        ]);

        // Jika ada insiden aktif, user tidak bisa mengaktifkan sirine
        return response()->json([
            'can_activate'   => !$hasActiveIncident,  // ← Bisa aktif jika TIDAK ada laporan aktif
            'incident_count' => $activeIncidents->count(),
            'message'        => $hasActiveIncident
                ? 'Anda memiliki laporan aktif. Selesaikan laporan terlebih dahulu sebelum menyalakan sirine.'
                : 'Tidak ada laporan aktif. Anda dapat menyalakan sirine.',
            'debug' => [
                'user_id'          => $userId,
                'has_active_incident' => $hasActiveIncident,
                'active_incidents' => $activeIncidents->map(function ($incident) {
                    return [
                        'id'                => $incident->id,
                        'type'              => $incident->type,
                        'status'            => $incident->status,
                        'images'            => $incident->images,
                        'reported_at'       => $incident->reported_at ? $incident->reported_at->toDateTimeString() : null,
                        'hours_since_report' => $incident->reported_at ? now()->diffInHours($incident->reported_at) : null
                    ];
                })
            ]
        ]);
    }

    /**
     * Get image URLs for incident
     */
    public function getImages($id)
    {
        $incident = Incident::findOrFail($id);

        if ($incident->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'images' => $incident->image_urls,
        ]);
    }
}
