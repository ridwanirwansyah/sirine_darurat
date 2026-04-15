<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\AlarmLog;  // ← Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncidentController extends Controller
{
    /**
     * Menampilkan halaman manajemen laporan kejadian
     */
    public function index(Request $request)
    {
        try {
            // Ambil parameter filter dari request
            $status = $request->input('status', 'all');
            $type = $request->input('type', 'all');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $search = $request->input('search');

            // Query dasar dengan eager loading user
            $query = Incident::with('user')
                ->orderBy('reported_at', 'desc');

            // Filter berdasarkan status
            if ($status && $status !== 'all') {
                if ($status === 'PENDING') {
                    $query->where('status', 'ACTIVE');
                } else {
                    $query->where('status', $status);
                }
            }

            // Filter berdasarkan jenis kejadian
            if ($type && $type !== 'all') {
                $query->where('type', $type);
            }

            // Filter berdasarkan tanggal
            if ($startDate) {
                $query->whereDate('reported_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('reported_at', '<=', $endDate);
            }

            // Filter berdasarkan pencarian
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('location', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            // Pagination
            $perPage = 10;
            $incidents = $query->paginate($perPage);
            $incidents->appends($request->all());

            // Hitung statistik
            $totalIncidents = Incident::count();
            $activeCount = Incident::where('status', 'ACTIVE')->count();
            $resolvedCount = Incident::where('status', 'RESOLVED')->count();
            $falseAlarmCount = Incident::where('status', 'FALSE_ALARM')->count();

            // Data untuk filter dropdown
            $statusOptions = [
                'ACTIVE' => 'Active/Pending',
                'RESOLVED' => 'Resolved',
                'FALSE_ALARM' => 'False Alarm'
            ];

            $typeOptions = [
                'KEBAKARAN' => 'Kebakaran',
                'PENCURIAN' => 'Pencurian',
                'GEMPA_BUMI' => 'Gempa Bumi',
                'BANJIR' => 'Banjir',
                'KECELAKAAN' => 'Kecelakaan',
                'PENYERANGAN' => 'Penyerangan',
                'GANGGUAN_KEAMANAN' => 'Gangguan Keamanan',
                'LAINNYA' => 'Lainnya'
            ];

            return view('admin.laporan_kejadian', compact(
                'incidents',
                'totalIncidents',
                'activeCount',
                'resolvedCount',
                'falseAlarmCount',
                'statusOptions',
                'typeOptions',
                'status',
                'type',
                'startDate',
                'endDate',
                'search'
            ));
        } catch (\Exception $e) {
            Log::error('Error in IncidentController@index: ' . $e->getMessage());

            return view('admin.laporan_kejadian', [
                'incidents' => collect(),
                'totalIncidents' => 0,
                'activeCount' => 0,
                'resolvedCount' => 0,
                'falseAlarmCount' => 0,
                'statusOptions' => [],
                'typeOptions' => [],
                'status' => 'all',
                'type' => 'all',
                'startDate' => null,
                'endDate' => null,
                'search' => null,
            ]);
        }
    }

    /**
     * Menampilkan detail laporan kejadian
     */
    public function show($id)
    {
        try {
            $incident = Incident::with('user')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $incident,
                'image_urls' => $incident->image_urls,
                'type_label' => $incident->getTypeLabel(),
                'status_label' => $incident->getStatusLabel(),
                'reported_at_formatted' => $incident->reported_at ? $incident->reported_at->format('d M Y, H:i') : '-',
                'resolved_at_formatted' => $incident->resolved_at ? $incident->resolved_at->format('d M Y, H:i') : '-',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in IncidentController@show: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Memperbarui status laporan kejadian
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:ACTIVE,RESOLVED,FALSE_ALARM',
                'notes' => 'nullable|string|max:500',
            ]);

            $incident = Incident::findOrFail($id);

            // Simpan data lama sebelum update
            $oldStatus = $incident->status;
            $oldResolvedAt = $incident->resolved_at;
            $oldNotes = $incident->resolution_notes;

            // Update status
            $incident->status = $request->status;

            // Jika status diubah menjadi RESOLVED atau FALSE_ALARM, set resolved_at
            if (in_array($request->status, ['RESOLVED', 'FALSE_ALARM']) && !$incident->resolved_at) {
                $incident->resolved_at = now();
            }

            // Jika status diubah kembali ke ACTIVE, reset resolved_at
            if ($request->status === 'ACTIVE') {
                $incident->resolved_at = null;
            }

            // Simpan catatan jika ada
            if ($request->filled('notes')) {
                $incident->resolution_notes = $request->notes;
            }

            $incident->save();

            // ✅ LOG: Catat aktivitas update status incident
            $statusLabels = [
                'ACTIVE' => 'Active/Pending',
                'RESOLVED' => 'Resolved',
                'FALSE_ALARM' => 'False Alarm'
            ];

            $oldStatusLabel = $statusLabels[$oldStatus] ?? $oldStatus;
            $newStatusLabel = $statusLabels[$request->status] ?? $request->status;

            AlarmLog::record([
                'action' => 'UPDATE_INCIDENT_STATUS',
                'target_type' => 'Incident',
                'target_id' => $incident->id,
                'description' => "Mengubah status laporan #{$incident->id} dari {$oldStatusLabel} menjadi {$newStatusLabel}",
                'old_data' => [
                    'status' => $oldStatus,
                    'resolved_at' => $oldResolvedAt,
                    'resolution_notes' => $oldNotes
                ],
                'new_data' => [
                    'status' => $incident->status,
                    'resolved_at' => $incident->resolved_at,
                    'resolution_notes' => $incident->resolution_notes
                ],
                'details' => [
                    'incident_id' => $incident->id,
                    'incident_type' => $incident->type,
                    'incident_location' => $incident->location,
                    'reported_by' => $incident->user->name ?? 'Unknown',
                    'updated_by' => auth()->user()->name,
                    'ip_address' => $request->ip()
                ]
            ]);

            Log::info("Incident {$id} status updated from {$oldStatus} to {$request->status} by " . auth()->user()->name);

            return response()->json([
                'success' => true,
                'message' => 'Status laporan berhasil diperbarui',
                'data' => $incident
            ]);
        } catch (\Exception $e) {
            Log::error('Error in IncidentController@update: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengekspor data ke CSV
     */
    public function exportCSV(Request $request)
    {
        try {
            // Apply filters sama seperti di index
            $query = Incident::with('user')->orderBy('reported_at', 'desc');

            if ($request->has('status') && $request->status !== 'all') {
                if ($request->status === 'PENDING') {
                    $query->where('status', 'ACTIVE');
                } else {
                    $query->where('status', $request->status);
                }
            }

            if ($request->has('type') && $request->type !== 'all') {
                $query->where('type', $request->type);
            }

            if ($request->has('start_date')) {
                $query->whereDate('reported_at', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->whereDate('reported_at', '<=', $request->end_date);
            }

            $incidents = $query->get();

            $filename = "laporan_kejadian_" . date('Y-m-d_H-i-s') . ".csv";

            // Create StreamedResponse
            $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($incidents) {
                $file = fopen('php://output', 'w');

                // UTF-8 BOM untuk Excel
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // 🔥 PERBAIKAN: Gunakan titik koma (;) sebagai delimiter untuk Excel
                // Header CSV
                fputcsv($file, [
                    'ID',
                    'Nama Pelapor',
                    'Email Pelapor',
                    'Jenis Kejadian',
                    'Lokasi',
                    'Deskripsi',
                    'Status',
                    'Dilaporkan Pada',
                    'Diselesaikan Pada',
                    'Catatan Resolusi'
                ], ';', '"');  // ← Gunakan ; sebagai delimiter

                // Data
                foreach ($incidents as $incident) {
                    $userName = $incident->user->name ?? 'N/A';
                    $userEmail = $incident->user->email ?? 'N/A';

                    fputcsv($file, [
                        $incident->id,
                        $userName,
                        $userEmail,
                        $incident->getTypeLabel(),
                        $incident->location,
                        $incident->description,
                        $incident->getStatusLabel(),
                        $incident->reported_at ? $incident->reported_at->format('d/m/Y H:i:s') : '-',
                        $incident->resolved_at ? $incident->resolved_at->format('d/m/Y H:i:s') : '-',
                        $incident->resolution_notes ?? '-'
                    ], ';', '"');  // ← Gunakan ; sebagai delimiter
                }

                fclose($file);
            });

            // Set headers
            $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');

            return $response;
        } catch (\Exception $e) {
            Log::error('Error in IncidentController@exportCSV: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    /**
     * Mengekspor data ke Excel
     */
  
    public function exportExcel(Request $request)
    {
        try {
            // Apply filters sama seperti di index
            $query = Incident::with('user')->orderBy('reported_at', 'desc');

            if ($request->has('status') && $request->status !== 'all') {
                if ($request->status === 'PENDING') {
                    $query->where('status', 'ACTIVE');
                } else {
                    $query->where('status', $request->status);
                }
            }

            if ($request->has('type') && $request->type !== 'all') {
                $query->where('type', $request->type);
            }

            if ($request->has('start_date')) {
                $query->whereDate('reported_at', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->whereDate('reported_at', '<=', $request->end_date);
            }

            $incidents = $query->get();

            $filename = "laporan_kejadian_" . date('Y-m-d_H-i-s') . ".csv";

            // 🔥 PERBAIKAN: Gunakan CSV dengan delimiter ; (kompatibel dengan Excel)
            $rows = [];

            // Header row (Bahasa Indonesia)
            $rows[] = [
                'ID',
                'Nama Pelapor',
                'Email Pelapor',
                'Jenis Kejadian',
                'Lokasi',
                'Deskripsi',
                'Status',
                'Dilaporkan Pada',
                'Diselesaikan Pada',
                'Catatan Resolusi'
            ];

            // Data rows
            foreach ($incidents as $incident) {
                $userName = $incident->user->name ?? 'N/A';
                $userEmail = $incident->user->email ?? 'N/A';

                $rows[] = [
                    $incident->id,
                    $userName,
                    $userEmail,
                    $incident->getTypeLabel(),
                    $incident->location,
                    $incident->description,
                    $incident->getStatusLabel(),
                    $incident->reported_at ? $incident->reported_at->format('d/m/Y H:i:s') : '-',
                    $incident->resolved_at ? $incident->resolved_at->format('d/m/Y H:i:s') : '-',
                    $incident->resolution_notes ?? '-'
                ];
            }

            // Generate CSV dengan delimiter titik koma (;)
            $csvContent = '';
            foreach ($rows as $row) {
                $csvContent .= implode(';', array_map(function ($value) {
                    // Escape CSV values (jika ada delimiter atau kutip)
                    $value = str_replace('"', '""', $value);
                    // Jika ada titik koma atau kutip, bungkus dengan kutip
                    if (strpos($value, ';') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
                        return '"' . $value . '"';
                    }
                    return $value;
                }, $row)) . "\n";
            }

            // Tambahkan BOM untuk UTF-8
            $csvContent = chr(0xEF) . chr(0xBB) . chr(0xBF) . $csvContent;

            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            return response($csvContent, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error in IncidentController@exportExcel: ' . $e->getMessage());

            // Fallback ke CSV jika ada error
            return $this->exportCSV($request);
        }
    }
}
