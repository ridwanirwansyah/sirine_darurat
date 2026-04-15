<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AlarmLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RiwayatController extends Controller
{
    /**
     * Tampilkan halaman riwayat aktivitas.
     */
    public function index(Request $request)
    {
        // ========== QUERY UNTUK RIWAYAT SIRINE (TABEL PERTAMA) ==========
        // Menampilkan ALARM_ON, ALARM_OFF, dan AUTO_OFF
        $query = AlarmLog::with('user')
            ->whereNotNull('event_time')
            ->whereIn('action', ['ALARM_ON', 'ALARM_OFF', 'AUTO_OFF'])
            ->orderBy('event_time', 'desc');

        // Filter berdasarkan kata kunci
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'LIKE', "%{$search}%")
                    ->orWhere('ip_address', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Filter berdasarkan jenis / tipe action (termasuk AUTO_OFF)
        if ($request->filled('type')) {
            $query->where('action', $request->type);
        }

        // Filter berdasarkan rentang tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('event_time', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        // ========== PERUBAHAN DI SINI ==========
        // Ubah dari paginate(20) menjadi paginate(50)
        // Dengan 50 item per halaman, total data akan menghasilkan lebih sedikit halaman
        $perPage = $request->input('per_page', 50); // Bisa diatur dari request, default 50
        $logs = $query->paginate($perPage);
        $logs->appends($request->query());

        // ========== QUERY UNTUK SEMUA LOG (TABEL LOGGING / KEDUA) ==========
        // Menampilkan SEMUA log (termasuk AUTO_OFF, CREATE_USER, dll)
        $allLogsQuery = AlarmLog::with('user')
            ->whereNotNull('event_time')
            ->orderBy('event_time', 'desc');

        // Filter untuk tabel logging
        if ($request->filled('log_search')) {
            $logSearch = $request->log_search;
            $allLogsQuery->where(function ($q) use ($logSearch) {
                $q->where('action', 'LIKE', "%{$logSearch}%")
                    ->orWhere('description', 'LIKE', "%{$logSearch}%")
                    ->orWhere('ip_address', 'LIKE', "%{$logSearch}%")
                    ->orWhereHas('user', function ($u) use ($logSearch) {
                        $u->where('name', 'LIKE', "%{$logSearch}%");
                    });
            });
        }

        if ($request->filled('log_action')) {
            $allLogsQuery->where('action', $request->log_action);
        }

        // ========== PERUBAHAN DI SINI ==========
        // Ubah dari paginate(20) menjadi paginate(50)
        $allLogs = $allLogsQuery->paginate($perPage);
        $allLogs->appends($request->query());

        // ========== STATISTIK ==========
        $totalLogs = AlarmLog::whereNotNull('event_time')->count();
        $todayLogs = AlarmLog::whereNotNull('event_time')
            ->whereDate('event_time', today())
            ->count();
        $sirineLogs = AlarmLog::whereNotNull('event_time')
            ->whereIn('action', ['ALARM_ON', 'ALARM_OFF', 'AUTO_OFF'])
            ->count();

        // Hitung log lainnya
        $autoOffLogs = AlarmLog::whereNotNull('event_time')
            ->where('action', 'AUTO_OFF')
            ->count();
        $userManagementLogs = AlarmLog::whereNotNull('event_time')
            ->whereIn('action', ['CREATE_USER', 'UPDATE_USER', 'DELETE_USER'])
            ->count();
        $incidentLogs = AlarmLog::whereNotNull('event_time')
            ->whereIn('action', ['CREATE_INCIDENT', 'UPDATE_INCIDENT_STATUS', 'DELETE_INCIDENT'])
            ->count();

        $lastActivityTime = AlarmLog::whereNotNull('event_time')
            ->latest('event_time')
            ->first()?->event_time;

        // ========== KIRIM KE VIEW ==========
        return view('admin.riwayat', compact(
            'logs',
            'totalLogs',
            'todayLogs',
            'sirineLogs',
            'autoOffLogs',
            'userManagementLogs',
            'incidentLogs',
            'lastActivityTime',
            'allLogs'
        ));
    }

    /**
     * Export data riwayat dalam berbagai format
     */
    public function export(Request $request)
    {
        $format = $request->query('format', 'csv');
        $exportType = $request->query('export_type', 'sirine'); // sirine atau system

        if ($exportType === 'system') {
            return $this->exportSystemLogs($request, $format);
        }

        return $this->exportSirineLogs($request, $format);
    }

    /**
     * Export data sirine riwayat ke CSV dengan kolom terpisah, khusus untuk log sirine (ALARM_ON, ALARM_OFF, AUTO_OFF) yang ditampilkan di tabel pertama, dengan filter yang sama seperti di halaman riwayat, dan menggunakan separator yang sesuai untuk Excel Indonesia (;) agar data tidak terpisah ke kolom yang berbeda ketika dibuka di Excel
      * fungsi ini juga melakukan pembersihan data untuk memastikan deskripsi tidak mengandung karakter yang bisa merusak format CSV, seperti new line atau tanda kutip yang tidak diinginkan
       * fungsi ini akan digunakan untuk export riwayat aktivitas sirine saja, sedangkan untuk export semua log sistem akan menggunakan fungsi exportSystemLogs() yang memiliki kolom lebih lengkap
     */
    private function exportSirineLogs($request, $format)
    {
        $query = AlarmLog::with('user')
            ->whereNotNull('event_time')
            ->whereIn('action', ['ALARM_ON', 'ALARM_OFF', 'AUTO_OFF'])
            ->orderBy('event_time', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'LIKE', "%{$search}%")
                    ->orWhere('ip_address', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('type')) {
            $query->where('action', $request->type);
        }

        $logs = $query->get();

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "Riwayat_Sirine_{$timestamp}";

        return $this->exportToCsvSirine($logs, $filename);
    }

    /**
     * Export data semua logs dengan format yang lebih lengkap, termasuk jenis aktivitas, pengguna, IP address, dan deskripsi, serta menggunakan separator yang sesuai untuk Excel Indonesia (;) agar data tidak terpisah ke kolom yang berbeda ketika dibuka di Excel
      * fungsi ini juga melakukan pembersihan data untuk memastikan deskripsi tidak mengandung karakter yang bisa merusak format CSV, seperti new line atau tanda kutip yang tidak diinginkan
       * fungsi ini akan digunakan untuk export riwayat aktivitas sistem secara keseluruhan, termasuk log sirine dan log lainnya seperti manajemen pengguna, laporan insiden, dll
     */
    private function exportSystemLogs($request, $format)
    {
        $query = AlarmLog::with('user')
            ->whereNotNull('event_time')
            ->orderBy('event_time', 'desc');

        // Apply filters
        if ($request->filled('log_search')) {
            $logSearch = $request->log_search;
            $query->where(function ($q) use ($logSearch) {
                $q->where('action', 'LIKE', "%{$logSearch}%")
                    ->orWhere('description', 'LIKE', "%{$logSearch}%")
                    ->orWhere('ip_address', 'LIKE', "%{$logSearch}%")
                    ->orWhereHas('user', function ($u) use ($logSearch) {
                        $u->where('name', 'LIKE', "%{$logSearch}%");
                    });
            });
        }

        if ($request->filled('log_action')) {
            $query->where('action', $request->log_action);
        }

        $logs = $query->get();

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "Riwayat_Sistem_{$timestamp}";

        return $this->exportToCsvSystem($logs, $filename);
    }

    /**
     * Export Sirine ke CSV dengan kolom terpisah
     */
    private function exportToCsvSirine($logs, $filename)
    {
        // Set headers untuk download CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM untuk support Unicode
            fwrite($file, "\xEF\xBB\xBF");

            // Header dengan kolom terpisah - gunakan separator yang jelas
            $headers = [
                'No',
                'Tanggal',
                'Waktu',
                'Pengguna',
                'Jenis Aktivitas',
                'IP Address',
                'Deskripsi'
            ];
            fputcsv($file, $headers, ';'); // Gunakan ; sebagai separator untuk Excel Indonesia

            // Data rows
            foreach ($logs as $index => $log) {
                $row = [
                    $index + 1,
                    $this->formatDateTime($log->event_time, 'd-m-Y'),
                    $this->formatDateTime($log->event_time, 'H:i:s'),
                    $log->user->name ?? 'Unknown',
                    $this->getActionLabel($log->action),
                    $log->ip_address ?? '-',
                    $this->cleanCsvField($log->description ?? '-')
                ];
                fputcsv($file, $row, ';'); // Gunakan ; sebagai separator
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * fungsi untuk export semua log sistem ke CSV dengan kolom yang lebih lengkap, termasuk jenis aktivitas, pengguna, IP address, dan deskripsi, serta menggunakan separator yang sesuai untuk Excel Indonesia (;) agar data tidak terpisah ke kolom yang berbeda ketika dibuka di Excel
      * fungsi ini juga melakukan pembersihan data untuk memastikan deskripsi tidak mengandung karakter yang bisa merusak format CSV, seperti new line atau tanda kutip yang tidak diinginkan
       * fungsi ini akan digunakan untuk export riwayat aktivitas sistem secara keseluruhan, termasuk log sirine dan log lainnya seperti manajemen pengguna, laporan insiden, dll
     */
    private function exportToCsvSystem($logs, $filename)
    {
        // Set headers untuk download CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM untuk support Unicode
            fwrite($file, "\xEF\xBB\xBF");

            // Header dengan kolom lebih lengkap
            $headers = [
                'No',
                'Tanggal',
                'Waktu',
                'Pengguna',
                'Aksi',
                'Keterangan',
                'IP Address',
                'Target Type',
                'Target ID'
            ];
            fputcsv($file, $headers, ';'); // Gunakan ; sebagai separator

            // Data rows
            foreach ($logs as $index => $log) {
                $row = [
                    $index + 1,
                    $this->formatDateTime($log->event_time, 'd-m-Y'),
                    $this->formatDateTime($log->event_time, 'H:i:s'),
                    $log->user->name ?? 'Sistem',
                    $this->getActionLabel($log->action),
                    $this->cleanCsvField($log->description ?? '-'),
                    $log->ip_address ?? '-',
                    $log->target_type ?? '-',
                    $log->target_id ?? '-'
                ];
                fputcsv($file, $row, ';'); // Gunakan ; sebagai separator
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clean CSV field from unwanted characters
     */
    private function cleanCsvField($field)
    {
        // Remove HTML tags
        $field = strip_tags($field);
        // Remove multiple spaces
        $field = preg_replace('/\s+/', ' ', $field);
        // Remove new lines
        $field = str_replace(["\r", "\n", "\r\n"], ' ', $field);
        // Trim
        $field = trim($field);

        return $field;
    }

    /**
     * Format waktu dengan pengecekan null
     */
    private function formatDateTime($dateTime, $format = 'd-m-Y H:i:s')
    {
        if (!$dateTime) {
            return '-';
        }

        if ($dateTime instanceof Carbon || $dateTime instanceof \DateTime) {
            return $dateTime->format($format);
        }

        try {
            return Carbon::parse($dateTime)->format($format);
        } catch (\Exception $e) {
            return '-';
        }
    }

    /**
     * Get human-readable action label (Bahasa Indonesia)
     */
    private function getActionLabel($action)
    {
        $labels = [
            'ALARM_ON' => 'Sirine Menyala',
            'ALARM_OFF' => 'Sirine Mati Manual',
            'AUTO_OFF' => 'Mati Otomatis',
            'PANIC_ACTIVATION' => 'Aktivasi Darurat',
            'CREATE_USER' => 'Tambah Pengguna',
            'UPDATE_USER' => 'Perbarui Pengguna',
            'DELETE_USER' => 'Hapus Pengguna',
            'CREATE_INCIDENT' => 'Laporan Baru',
            'UPDATE_INCIDENT' => 'Perbarui Laporan',
            'DELETE_INCIDENT' => 'Hapus Laporan',
            'UPDATE_INCIDENT_STATUS' => 'Perbarui Status Laporan',
            'LOGIN' => 'Masuk Sistem',
            'LOGOUT' => 'Keluar Sistem',
            'EXPORT_INCIDENTS' => 'Ekspor Laporan',
            'UPDATE_AUTO_OFF_DURATION' => 'Perbarui Durasi Mati Otomatis',
            'AUTO_EMERGENCY_INCIDENT' => 'Laporan Darurat Otomatis',
            'DELETE_INCIDENT_IMAGE' => 'Hapus Gambar Laporan',
            'RESOLVE_INCIDENT' => 'Selesaikan Laporan',
            'FALSE_ALARM_INCIDENT' => 'Alarm Palsu',
        ];

        return $labels[$action] ?? str_replace('_', ' ', $action);
    }

    /**
     * Hapus log riwayat tertentu.
     */
    public function destroy($id)
    {
        try {
            $log = AlarmLog::findOrFail($id);
            $log->delete();

            return redirect()->back()->with('success', 'Riwayat berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Delete log error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus riwayat.');
        }
    }

    /**
     * Hapus semua riwayat (opsional).
     */
    public function clearAll()
    {
        try {
            AlarmLog::whereNotNull('event_time')->delete();

            return redirect()->back()->with('success', 'Semua riwayat aktivitas telah dihapus.');
        } catch (\Exception $e) {
            Log::error('Clear all logs error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus semua riwayat.');
        }
    }
}