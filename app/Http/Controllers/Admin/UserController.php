<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use App\Models\AlarmLog;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Show all users.
     */
    public function index()
    {
        $users = User::where('role', 'user')->latest()->get();

        return view('admin.pengguna', compact('users'));
    }

    /**
     * Show form to create new user.
     */
    public function create()
    {
        return view('admin.pengguna_create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,guru,user',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|in:0,1',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'is_active' => $request->is_active,
            'unique_id' => $this->generateUniqueId(),
            'pin' => $this->generatePin(),
        ]);

        // ✅ LOG: Catat aktivitas tambah user
        AlarmLog::record([
            'action' => 'CREATE_USER',
            'target_type' => 'User',
            'target_id' => $user->id,
            'description' => "Menambahkan user baru: {$user->name} ({$user->email}) dengan role " . ucfirst($user->role),
            'new_data' => $user->toArray(),
            'details' => [
                'user_role' => $user->role,
                'created_by' => auth()->user()->name,
                'ip_address' => $request->ip()
            ]
        ]);

        return redirect()->route('admin.pengguna')
            ->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Show form to edit user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.pengguna_edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $oldData = $user->toArray();

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($user->id)
                ],
                'role' => 'required|in:admin,guru,user',
                'phone' => 'nullable|string|max:20',
                'is_active' => 'required|in:0,1',
                'password' => 'nullable|min:6',
            ]);

            $updateData = $request->only(['name', 'email', 'role', 'phone', 'is_active']);

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // ✅ LOG: Catat aktivitas update user
            AlarmLog::record([
                'action' => 'UPDATE_USER',
                'target_type' => 'User',
                'target_id' => $user->id,
                'description' => "Admin mengedit user: {$user->name} ({$user->email})",
                'old_data' => $oldData,
                'new_data' => $user->toArray(),
                'details' => [
                    'updated_by' => auth()->user()->name,
                    'changed_fields' => array_keys($request->only(['name', 'email', 'role', 'phone', 'is_active']))
                ]
            ]);

            return redirect()->route('admin.pengguna')
                ->with('success', 'User berhasil diedit');  // ← NOTIFIKASI DIUBAH

        } catch (\Exception $e) {
            Log::error('Update user error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengedit user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * fungsi untuk menghapus user, dengan pengecekan untuk mencegah admin menghapus dirinya sendiri, dan mencatat log aktivitas penghapusan user dengan detail yang lengkap, termasuk data user yang dihapus sebelum dihapus, serta informasi tentang admin yang melakukan penghapusan
     */
    public function destroy($id)
    {
        try {
            // Cegah menghapus diri sendiri
            if ($id == auth()->id()) {
                return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun sendiri');
            }

            $user = User::findOrFail($id);
            $userName = $user->name;
            $userEmail = $user->email;

            // ✅ LOG: Catat aktivitas hapus user (sebelum delete)
            AlarmLog::record([
                'action' => 'DELETE_USER',
                'target_type' => 'User',
                'target_id' => $user->id,
                'description' => "Menghapus user: {$userName} ({$userEmail})",
                'old_data' => $user->toArray(),
                'details' => [
                    'deleted_by' => auth()->user()->name,
                    'deleted_user_role' => $user->role
                ]
            ]);

            $user->delete();

            return redirect()->route('admin.pengguna')
                ->with('success', 'User berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Delete user error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    /**
     * fungsi untuk toggle status aktif/nonaktif user
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);

            // Cegah menonaktifkan diri sendiri
            if ($id == auth()->id()) {
                return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri');
            }

            $oldStatus = $user->is_active;
            $newStatus = !$oldStatus;

            $user->update(['is_active' => $newStatus]);

            // ✅ LOG: Catat aktivitas toggle status user
            $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
            AlarmLog::record([
                'action' => 'UPDATE_USER',
                'target_type' => 'User',
                'target_id' => $user->id,
                'description' => "{$statusText} user: {$user->name} ({$user->email})",
                'old_data' => ['is_active' => $oldStatus],
                'new_data' => ['is_active' => $newStatus],
                'details' => [
                    'action_by' => auth()->user()->name
                ]
            ]);

            $message = $newStatus ? 'User berhasil diaktifkan' : 'User berhasil dinonaktifkan';
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Toggle status error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengubah status user');
        }
    }

    /**
     * Export users to CSV
     */
    public function export()
    {
        $users = User::orderBy('id', 'asc')->get();

        $filename = 'users_' . date('Y-m-d_H-i-s') . '.csv';

        // Create CSV content
        $csv = chr(0xEF) . chr(0xBB) . chr(0xBF); // UTF-8 BOM

        // Header dengan kolom baru
        $csv .= implode(';', [
            'ID',
            'UNIQUE_ID',
            'Nama',
            'Email',
            'No. Telepon',
            'Role',
            'Status',
            'PIN',
            'Tanggal Dibuat'
        ]) . "\n";

        // Data
        foreach ($users as $user) {
            $csv .= implode(';', [
                $user->id,
                $user->unique_id ?? '',
                $user->name,
                $user->email,
                $user->phone ?? '',
                $user->role,
                $user->is_active ? 'Aktif' : 'Tidak Aktif',
                $user->pin ?? '',
                $user->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * Import users from CSV or Excel (XLSX only)
     * Password default: password123 (lowercase)
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt,xlsx|max:5120',
            ]);

            $file = $request->file('file');
            $filePath = $file->getRealPath();
            $fileExtension = strtolower($file->getClientOriginalExtension());

            $data_array = [];

            // Handle Excel files (.xlsx)
            if ($fileExtension === 'xlsx') {
                try {
                    $data_array = $this->parseXlsx($filePath);
                    if (empty($data_array)) {
                        return redirect()->route('admin.pengguna')->with('error', 'File Excel kosong atau tidak valid');
                    }
                } catch (\Exception $e) {
                    return redirect()->route('admin.pengguna')->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
                }
            } else {
                // Handle CSV/TXT files
                $stream = fopen($filePath, 'r');
                if (!$stream) {
                    return redirect()->route('admin.pengguna')->with('error', 'Gagal membuka file');
                }
                while ($row_data = fgetcsv($stream, null, ';')) {
                    $data_array[] = $row_data;
                }
                fclose($stream);
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $existingUniqueIds = User::where('role', 'user')->pluck('unique_id')->toArray();

            // 🔥 PASSWORD DEFAULT (lowercase, mudah diingat)
            $defaultPassword = 'password123';
            $hashedPassword = Hash::make($defaultPassword);

            // Skip header (first row)
            $skip_first = true;
            $row = 0;

            foreach ($data_array as $data) {
                // Skip header row
                if ($skip_first) {
                    $skip_first = false;
                    continue;
                }

                $row++;

                // Skip empty rows
                if (empty(array_filter($data))) {
                    continue;
                }

                try {
                    // Validasi jumlah kolom (minimal 8 kolom)
                    if (count($data) < 8) {
                        throw new \Exception('Format file tidak sesuai. Minimal 8 kolom (ID, UNIQUE_ID, Nama, Email, No HP, Role, Status, PIN)');
                    }

                    // Map data: ID, UNIQUE_ID, Nama, Email, No HP, Role, Status, PIN
                    $uniqueId = isset($data[1]) ? trim($data[1]) : '';
                    $name = isset($data[2]) ? trim($data[2]) : '';
                    $email = isset($data[3]) ? trim($data[3]) : '';
                    $phone = isset($data[4]) ? trim($data[4]) : '';
                    $role = isset($data[5]) ? strtoupper(trim($data[5])) : '';
                    $statusText = isset($data[6]) ? strtolower(trim($data[6])) : '';
                    $pin = isset($data[7]) ? trim($data[7]) : '';

                    // Validate required fields
                    if (empty($name) || empty($email) || empty($role)) {
                        throw new \Exception('Nama, Email, dan Role harus diisi');
                    }

                    // Check if email already exists
                    if (User::where('email', $email)->exists()) {
                        throw new \Exception("Email $email sudah terdaftar");
                    }

                    // Validate role (hanya USER untuk import ke tabel user)
                    if ($role !== 'USER') {
                        throw new \Exception("Role $role tidak valid. Import hanya untuk role USER");
                    }

                    // Extract is_active
                    $is_active = 1;
                    if (!empty($statusText)) {
                        $is_active = in_array($statusText, ['aktif', 'active', '1', 'yes', 'true']) ? 1 : 0;
                    }

                    // Handle unique_id
                    if (empty($uniqueId)) {
                        do {
                            $uniqueId = str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
                        } while (in_array($uniqueId, $existingUniqueIds) || User::where('unique_id', $uniqueId)->exists());
                    } else {
                        if (!preg_match('/^\d{2}$/', $uniqueId)) {
                            throw new \Exception("Unique ID $uniqueId harus 2 digit angka");
                        }
                        if (in_array($uniqueId, $existingUniqueIds) || User::where('unique_id', $uniqueId)->exists()) {
                            throw new \Exception("Unique ID $uniqueId sudah digunakan");
                        }
                    }

                    // Handle PIN
                    if (empty($pin)) {
                        $pin = sprintf("%06d", mt_rand(1, 999999));
                    } else {
                        if (!preg_match('/^\d{6}$/', $pin)) {
                            throw new \Exception("PIN harus 6 digit angka");
                        }
                    }

                    // Buat user baru dengan role 'user' dan password default
                    User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => $hashedPassword,  // ← password: password123
                        'phone' => $phone ?: null,
                        'role' => 'user',  // ← force role 'user'
                        'is_active' => $is_active,
                        'unique_id' => $uniqueId,
                        'pin' => $pin,
                    ]);

                    $existingUniqueIds[] = $uniqueId;
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Baris $row: {$e->getMessage()}";
                }
            }

            $message = "Import selesai. {$successCount} user berhasil ditambahkan dengan password default: {$defaultPassword}";

            if ($errorCount > 0) {
                $message .= " {$errorCount} error.";
            }

            // Log import activity
            AlarmLog::record([
                'action' => 'IMPORT_USERS',
                'target_type' => 'User',
                'description' => "Import data pengguna: {$successCount} berhasil, {$errorCount} gagal",
                'details' => [
                    'imported_by' => auth()->user()->name,
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'default_password' => $defaultPassword,
                    'errors' => $errors
                ]
            ]);

            if ($errorCount > 0) {
                return redirect()->route('admin.pengguna')->with([
                    'success' => $message,
                    'import_errors' => $errors,
                ]);
            }

            return redirect()->route('admin.pengguna')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            return redirect()->route('admin.pengguna')->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique ID (2 digit angka)
     */
    private function generateUniqueId()
    {
        do {
            $uniqueId = str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
        } while (User::where('unique_id', $uniqueId)->exists());

        return $uniqueId;
    }

    /**
     * Generate PIN 6 digit
     */
    private function generatePin()
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Parse XLSX file without external library
     * XLSX is ZIP format, we extract XML and parse it
     */
    private function parseXlsx($filePath)
    {
        // Create temporary directory
        $tempDir = sys_get_temp_dir() . '/' . uniqid('xlsx_');
        mkdir($tempDir, 0777, true);

        try {
            // Extract ZIP
            $zip = new \ZipArchive();
            if (!$zip->open($filePath)) {
                throw new \Exception('Tidak bisa membuka file ZIP');
            }
            $zip->extractTo($tempDir);
            $zip->close();

            // Read sheet data
            $xmlFile = $tempDir . '/xl/worksheets/sheet1.xml';
            if (!file_exists($xmlFile)) {
                throw new \Exception('Struktur file Excel tidak valid');
            }

            $xmlContent = file_get_contents($xmlFile);
            $data = [];

            // Parse XML
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlContent);
            libxml_use_internal_errors(false);

            if (!$xml) {
                throw new \Exception('Gagal parse XML');
            }

            // Extract rows
            foreach ($xml->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $cell) {
                    $value = '';
                    if (isset($cell->v)) {
                        $value = (string)$cell->v;
                        // Handle shared strings
                        if ((string)$cell['t'] === 's') {
                            // Shared string reference - value is index
                            $stringFile = $tempDir . '/xl/sharedStrings.xml';
                            if (file_exists($stringFile)) {
                                $stringXml = simplexml_load_file($stringFile);
                                if (isset($stringXml->si[(int)$value]->t)) {
                                    $stringValue = $stringXml->si[(int)$value]->t;
                                    $value = (string)$stringValue;
                                }
                            }
                        }
                    }
                    $rowData[] = $value;
                }
                if (!empty(array_filter($rowData))) {
                    $data[] = $rowData;
                }
            }

            // Cleanup
            $this->deleteDirectory($tempDir);

            return $data;
        } catch (\Exception $e) {
            // Cleanup on error
            if (is_dir($tempDir)) {
                $this->deleteDirectory($tempDir);
            }
            throw $e;
        }
    }

    /**
     * Recursively delete directory
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) return;
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $this->deleteDirectory($path);
                } else {
                    unlink($path);
                }
            }
        }
        rmdir($dir);
    }
}
