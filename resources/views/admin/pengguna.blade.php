{{-- resources/views/admin/pengguna.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sisirine™ - Manajemen Akun</title>

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Global Styles --}}
    @include('admin.partials.styles')

    {{-- CSS Khusus Manajemen Pengguna --}}
    <style>
        /* Status Badges */
        .status-badge {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 9999px;
        }
        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-inactive {
            background-color: #fef3c7;
            color: #92400e;
        }
        .role-badge {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 9999px;
            font-weight: 500;
            display: inline-block;
            white-space: nowrap;
        }
        .role-admin {
            background: #dbeafe;
            color: #1e40af;
        }
        .role-guru {
            background: #f3e8ff;
            color: #7c3aed;
        }
        .role-user {
            background: #e0f2fe;
            color: #0284c7;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 600;
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.875rem;
        }
        tr:hover {
            background-color: #f8fafc;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 200;
            padding: 1rem;
        }
        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            width: 100%;
            max-width: 450px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .form-input {
            width: 100%;
            border: 1px solid #e2e8f0;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            ring: 2px solid #3b82f6;
        }
        .form-select {
            width: 100%;
            border: 1px solid #e2e8f0;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background: white;
        }
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        /* Action Buttons */
        .action-btn {
            padding: 6px 10px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
            background: transparent;
        }

        /* Dashboard Content */
        .dashboard-content {
            margin-left: 16rem;
            width: calc(100% - 16rem);
        }

        @media (max-width: 1024px) {
            .dashboard-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body class="bg-gray-50">

    <div class="flex min-h-screen">
        {{-- SIDEBAR --}}
        @include('admin.partials.sidebar')

        {{-- MAIN CONTENT --}}
        <div class="flex-1 flex flex-col dashboard-content">

            {{-- HEADER --}}
            @include('admin.partials.header', [
                'pageTitle' => 'Manajemen Akun',
                'pageDescription' => 'Kelola pengguna & hak akses sistem'
            ])

            {{-- MAIN CONTENT --}}
            <main class="main-scroll">
                @if(session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-700 text-sm">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    </p>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        @foreach($errors->all() as $error)
                        {{ $error }}<br>
                        @endforeach
                    </p>
                </div>
                @endif

                @if(session('import_errors') && count(session('import_errors')) > 0)
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 font-semibold text-sm mb-2">
                        <i class="fas fa-exclamation-circle mr-2"></i>Kesalahan dalam import:
                    </p>
                    <ul class="text-red-600 text-xs list-disc list-inside">
                        @foreach(session('import_errors') as $error)
                        <li class="mb-1">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Daftar Pengguna</h3>
                        <p class="text-xs text-gray-600">Total <span class="text-blue-600 font-bold">{{ $users->count() }}</span> pengguna</p>
                    </div>
                    <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                        <button type="button" onclick="openImportModal()" class="flex-1 sm:flex-none bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-xs flex items-center justify-center gap-1 transition">
                            <i class="fas fa-file-import"></i> Import Data
                        </button>
                        <a href="{{ route('admin.pengguna.export') }}" class="flex-1 sm:flex-none bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg text-xs flex items-center justify-center gap-1 transition">
                            <i class="fas fa-file-export"></i> Ekspor Data
                        </a>
                        <button type="button" onclick="openAddModal()" class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs flex items-center justify-center gap-1 transition">
                            <i class="fas fa-plus"></i> Tambah Pengguna
                        </button>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="table-container">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase whitespace-nowrap">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase whitespace-nowrap">ID Unik</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase whitespace-nowrap">PIN</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase whitespace-nowrap">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase whitespace-nowrap">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase whitespace-nowrap">No HP</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase whitespace-nowrap">Peran</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase whitespace-nowrap">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase whitespace-nowrap">Tanggal</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                @foreach($users as $u)
                                @php
                                $editData = base64_encode(json_encode([
                                    'id' => $u->id,
                                    'name' => $u->name,
                                    'email' => $u->email,
                                    'role' => $u->role,
                                    'phone' => $u->phone,
                                    'is_active' => $u->is_active,
                                    'unique_id' => $u->unique_id,
                                    'pin' => $u->pin
                                ]));
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 text-xs font-mono font-bold text-blue-600 whitespace-nowrap">{{ $u->unique_id ?? '-' }}</td>
                                    <td class="px-4 py-3 text-xs font-mono whitespace-nowrap">{{ $u->pin ?? '-' }}</td>
                                    <td class="px-4 py-3 text-xs font-semibold text-gray-800 whitespace-nowrap">{{ $u->name }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">{{ $u->email }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">{{ $u->phone ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="role-badge role-{{ strtolower($u->role) }}">{{ ucfirst($u->role) }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="status-badge {{ $u->is_active ? 'status-active' : 'status-inactive' }}">
                                            {{ $u->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">{{ $u->created_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <div class="flex justify-center gap-2">
                                            <button type="button"
                                                onclick="openEditModal('{{ $editData }}')"
                                                class="action-btn text-blue-600 hover:bg-blue-100 rounded-lg p-2"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button"
                                                onclick="openDeleteModal('{{ $u->id }}')"
                                                class="action-btn text-red-600 hover:bg-red-100 rounded-lg p-2"
                                                title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    {{-- MODAL TAMBAH AKUN --}}
    <div id="add-modal" class="modal-overlay">
        <div class="modal-content">
            <h2 class="text-lg font-bold mb-4">Tambah Akun</h2>

            <form action="{{ route('admin.pengguna.store') }}" method="POST">
                @csrf

                <input type="text" name="name" placeholder="Nama Lengkap" class="form-input" required>
                <input type="email" name="email" placeholder="Email" class="form-input" required>
                <input type="password" name="password" placeholder="Kata Sandi" class="form-input" required>
                <input type="text" name="phone" placeholder="No HP (Opsional)" class="form-input">

                <select name="role" class="form-select" required>
                    <option value="">Pilih Peran</option>
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                    <option value="user">Pengguna</option>
                </select>

                <select name="is_active" class="form-select" required>
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeAllModals()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg text-sm transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT AKUN (Menggunakan route terpisah /update) --}}
    <div id="edit-modal" class="modal-overlay">
        <div class="modal-content">
            <h2 class="text-lg font-bold mb-4">Edit Akun</h2>

            <form id="edit-form" method="POST">
                @csrf

                <input type="text" id="edit-name" name="name" placeholder="Nama" class="form-input" required>
                <input type="email" id="edit-email" name="email" placeholder="Email" class="form-input" required>
                <input type="text" id="edit-phone" name="phone" placeholder="No HP" class="form-input">

                <div class="grid grid-cols-2 gap-2">
                    <input type="text" id="edit-unique-id" name="unique_id" placeholder="ID Unik" class="form-input bg-gray-100" readonly>
                    <input type="text" id="edit-pin" name="pin" placeholder="PIN" class="form-input bg-gray-100" readonly>
                </div>

                <select id="edit-role" name="role" class="form-select" required>
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                    <option value="user">Pengguna</option>
                </select>

                <select id="edit-status" name="is_active" class="form-select" required>
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>

                <input type="password" name="password" placeholder="Kata Sandi baru (kosongkan jika tidak diubah)" class="form-input">

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeAllModals()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg text-sm transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL IMPORT PENGGUNA --}}
    <div id="import-modal" class="modal-overlay">
        <div class="modal-content">
            <h2 class="text-lg font-bold mb-3">Import Pengguna</h2>

            <form action="{{ route('admin.pengguna.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">File (CSV, TXT, atau Excel)</label>
                    <input type="file" name="file" accept=".csv,.txt,.xlsx" class="w-full border border-gray-300 p-2 rounded-lg text-sm" required>
                    <p class="text-xs text-gray-500 mt-1">Format: CSV/TXT/XLSX (pemisah: ;)</p>
                </div>

                <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs">
                    <p class="font-semibold text-blue-800 mb-2">📋 Format Data (8 Kolom):</p>
                    <p class="text-blue-700 font-mono text-xs mb-2">ID;ID_UNIK;Nama;Email;No HP;Peran;Status;PIN</p>
                    <p class="text-blue-700 mb-1"><span class="font-semibold">Contoh:</span></p>
                    <p class="text-blue-700 font-mono text-xs">1;01;John Doe;john@email.com;0812345;ADMIN;Aktif;123456</p>
                    <p class="text-blue-700 font-mono text-xs mt-1">2;02;Jane Smith;jane@email.com;0823456;USER;Aktif;789012</p>
                    <a href="{{ route('admin.template.users') }}" class="text-blue-600 hover:text-blue-800 font-semibold inline-block mt-2">
                        <i class="fas fa-download"></i> Unduh Template
                    </a>
                </div>

                <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-xs">
                    <p class="font-semibold text-yellow-800 mb-1">⚠️ Catatan:</p>
                    <ul class="text-yellow-700 list-disc list-inside space-y-0.5">
                        <li>ID_UNIK: 2 digit angka (01-99), harus unik</li>
                        <li>PIN: 6 digit angka (kosongkan untuk generate otomatis)</li>
                        <li>Peran: ADMIN, GURU, atau USER</li>
                        <li>Email harus unik</li>
                        <li>Kata Sandi default: Password123!</li>
                    </ul>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-200">
                    <button type="button" onclick="closeImportModal()" class="px-3 py-2 text-sm bg-gray-300 hover:bg-gray-400 rounded-lg transition">Batal</button>
                    <button type="submit" class="px-3 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition">Import</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL HAPUS AKUN (Menggunakan route terpisah /hapus) --}}
    <div id="delete-modal" class="modal-overlay">
        <div class="modal-content">
            <h2 class="text-lg font-bold mb-3">Hapus Akun?</h2>
            <p class="text-gray-600 text-sm mb-6">Apakah Anda yakin ingin menghapus akun ini? Tindakan ini tidak dapat dibatalkan.</p>

            <form id="delete-form" method="POST">
                @csrf
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeAllModals()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg text-sm transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Global Scripts --}}
    @include('admin.partials.scripts')

    {{-- JS KHUSUS MANAJEMEN PENGGUNA --}}
    <script>
        /**
         * Modal handlers untuk CRUD
         */
        function openAddModal() {
            const modal = document.getElementById('add-modal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function openImportModal() {
            const modal = document.getElementById('import-modal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeImportModal() {
            const modal = document.getElementById('import-modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        function openEditModal(encodedData) {
            try {
                const data = JSON.parse(atob(encodedData));
                console.log('Opening edit modal for ID:', data.id);

                // Gunakan route POST update terpisah seperti kode awal: /admin/pengguna/update/{id}
                document.getElementById('edit-form').action = "{{ url('/admin/pengguna/update') }}/" + data.id;
                document.getElementById('edit-name').value = data.name || '';
                document.getElementById('edit-email').value = data.email || '';
                document.getElementById('edit-phone').value = data.phone || '';
                document.getElementById('edit-role').value = data.role || '';
                document.getElementById('edit-status').value = data.is_active ? '1' : '0';
                document.getElementById('edit-unique-id').value = data.unique_id || '';
                document.getElementById('edit-pin').value = data.pin || '';

                const editModal = document.getElementById('edit-modal');
                if (editModal) {
                    editModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            } catch (error) {
                console.error('Error parsing edit data:', error);
                alert('Gagal memuat data pengguna');
            }
        }

        function openDeleteModal(id) {
            // Gunakan route DELETE terpisah seperti kode awal: /admin/pengguna/hapus/{id}
            document.getElementById('delete-form').action = "{{ url('/admin/pengguna/hapus') }}/" + id;
            const deleteModal = document.getElementById('delete-modal');
            if (deleteModal) {
                deleteModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeAllModals() {
            const modals = document.querySelectorAll('.modal-overlay');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeAllModals();
                }
            });
        });

        // Close modal with ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAllModals();
            }
        });

        // Inisialisasi saat DOM siap
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Manajemen Akun page loaded');
        });
    </script>
</body>
</html>