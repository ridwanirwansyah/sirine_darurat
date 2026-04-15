{{-- resources/views/admin/riwayat.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sisirine™ - Riwayat Aktivitas</title>

    {{-- Tailwind & Font Awesome --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Global Styles --}}
    @include('admin.partials.styles')

    {{-- CSS Khusus Riwayat (DIPERBAIKI) --}}
    <style>
        /* ============================================ */
        /* CSS KHUSUS RIWAYAT AKTIVITAS */
        /* ============================================ */

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            width: 100%;
            -webkit-overflow-scrolling: touch;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .table-container th {
            background-color: #f1f5f9;
            font-weight: 600;
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-container td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.875rem;
        }

        .table-container tr:hover {
            background-color: #f8fafc;
        }

        /* Log Type Badges */
        .log-type-badge {
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 9999px;
            font-weight: 500;
            display: inline-block;
        }

        .log-type-sirine {
            background: #dbeafe;
            color: #1e40af;
        }

        .log-type-laporan {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .log-type-sistem {
            background: #d1fae5;
            color: #065f46;
        }

        .log-type-keamanan {
            background: #fee2e2;
            color: #991b1b;
        }

        .log-type-akun {
            background: #fef3c7;
            color: #92400e;
        }

        /* Search Box */
        .search-box {
            position: relative;
        }

        .search-box input {
            padding-right: 2.5rem;
        }

        .search-box i {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
        }

        /* Pagination */
        .pagination {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            min-width: 2.5rem;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .pagination a {
            background-color: white;
            color: #1e40af;
            text-decoration: none;
            cursor: pointer;
            font-weight: 500;
        }

        .pagination a:hover {
            background-color: #dbeafe;
            border-color: #60a5fa;
            color: #1e40af;
        }

        .pagination span.active {
            background-color: #1e40af;
            color: white;
            border-color: #1e40af;
            font-weight: 600;
        }

        .pagination span.disabled {
            color: #9ca3af;
            background-color: #f3f4f6;
            border-color: #e5e7eb;
            cursor: not-allowed;
        }

        /* Line clamp */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Card styles */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .table-container table {
                min-width: 600px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        /* Aksi button */
        .view-detail-btn {
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .view-detail-btn:hover {
            background-color: #eff6ff;
        }

        /* Modal overlay fix */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 1rem;
            display: none;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
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
            'pageTitle' => 'Riwayat Aktivitas',
            'pageDescription' => 'Riwayat lengkap aktivitas sistem dan pengguna'
            ])

            {{-- MAIN CONTENT --}}
            <main class="main-scroll">

                {{-- STATISTIK SECTION --}}
                <div class="stats-grid mb-8">
                    {{-- Total Aktivitas Card --}}
                    <div class="bg-white rounded-xl p-5 shadow hover:shadow-lg transition border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Total Aktivitas</p>
                                <p class="text-3xl font-bold text-slate-800 mt-2">{{ $totalLogs ?? 0 }}</p>
                                <p class="text-xs text-gray-500 mt-1">Semua riwayat</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-history text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Aktivitas Hari Ini Card --}}
                    <div class="bg-white rounded-xl p-5 shadow hover:shadow-lg transition border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Aktivitas Hari Ini</p>
                                <p class="text-3xl font-bold text-green-600 mt-2">{{ $todayLogs ?? 0 }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ now()->translatedFormat('d M Y') }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-day text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Aktivitas Sirine Card --}}
                    <div class="bg-white rounded-xl p-5 shadow hover:shadow-lg transition border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Aktivitas Sirine</p>
                                <p class="text-3xl font-bold text-purple-600 mt-2">{{ $sirineLogs ?? 0 }}</p>
                                <p class="text-xs text-gray-500 mt-1">Menyala / Mati Otomatis</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-bullhorn text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TABLE SECTION --}}
                <div class="bg-white rounded-xl shadow-md border border-gray-200">

                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Riwayat Aktivitas</h3>
                            <p class="text-gray-600 text-sm">Total <span class="font-bold text-blue-600">{{ $totalLogs ?? 0 }}</span> log aktivitas</p>
                        </div>
                        <button onclick="openExportModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                            <i class="fas fa-download"></i> Ekspor
                        </button>
                    </div>

                    {{-- Filter --}}
                    <form id="filter-form" method="GET" action="{{ route('admin.riwayat') }}" class="px-6 pb-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="search-box md:col-span-2">
                                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                                    placeholder="Cari pengguna, aktivitas, atau alamat IP..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <i class="fas fa-search"></i>
                            </div>

                            <select name="type" id="filterType" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="">Semua Jenis</option>
                                <option value="ALARM_ON" {{ request('type') === 'ALARM_ON' ? 'selected' : '' }}>Sirine Menyala</option>
                                <option value="ALARM_OFF" {{ request('type') === 'ALARM_OFF' ? 'selected' : '' }}>Sirine Mati Manual</option>
                                <option value="AUTO_OFF" {{ request('type') === 'AUTO_OFF' ? 'selected' : '' }}>Mati Otomatis</option>
                            </select>

                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition flex items-center justify-center gap-1">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.riwayat') }}" class="flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg text-sm font-medium transition flex items-center justify-center gap-1">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    {{-- Table --}}
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th class="text-center w-16">No</th>
                                    <th>Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Jenis Aktivitas</th>
                                    <th>Alamat IP</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td class="text-center font-semibold text-gray-700">{{ (($logs->currentPage() - 1) * 20) + $loop->iteration }}</td>
                                    <td>
                                        <div>{{ $log->event_time ? $log->event_time->format('H:i:s') : '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->event_time ? $log->event_time->format('d M Y') : '-' }}</div>
                                    </td>
                                    <td class="font-semibold text-gray-800">{{ $log->user->name ?? 'Unknown' }}</td>
                                    <td>
                                        <span class="log-type-badge log-type-sirine">
                                            @if($log->action === 'ALARM_ON') Sirine Menyala
                                            @elseif($log->action === 'ALARM_OFF') Sirine Mati Manual
                                            @elseif($log->action === 'AUTO_OFF') Mati Otomatis
                                            @else {{ $log->action }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="font-mono">{{ $log->ip_address ?? '-' }}</td>
                                    <td class="text-center">
                                        <button type="button"
                                            data-id="{{ $log->id }}"
                                            data-name="{{ e($log->user->name ?? 'Unknown') }}"
                                            data-action="{{ e($log->action) }}"
                                            data-description="{{ e($log->description ?? '-') }}"
                                            data-time="{{ $log->event_time?->format('d M Y H:i:s') ?? '-' }}"
                                            data-ip="{{ e($log->ip_address ?? '-') }}"
                                            class="view-detail-btn text-blue-600 hover:text-blue-700">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-gray-500">
                                        <i class="fas fa-inbox text-3xl mb-2"></i>
                                        <p>Belum ada aktivitas</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="p-6 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <p class="text-sm text-gray-600">
                            Menampilkan <span class="font-semibold">{{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }}</span>
                            dari <span class="font-semibold">{{ $logs->total() }}</span> aktivitas
                        </p>
                        <div>
                            {{ $logs->links() }}
                        </div>
                    </div>

                </div>

                {{-- TABEL LOGGING AKTIVITAS SISTEM --}}
                <div class="bg-white rounded-xl shadow-md border border-gray-200 mt-8">

                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-clipboard-list text-purple-600"></i>
                            Pencatatan Aktivitas Sistem
                        </h3>
                        <p class="text-gray-600 text-sm mt-1">Semua aktivitas sistem termasuk sirine, pengguna, dan laporan kejadian</p>
                    </div>

                    {{-- Filter Logging --}}
                    <form id="filter-logging-form" method="GET" action="{{ route('admin.riwayat') }}" class="px-6 pb-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="search-box md:col-span-2">
                                <input type="text" name="log_search" id="logSearchInput" value="{{ request('log_search') }}"
                                    placeholder="Cari pengguna, aksi, deskripsi, atau alamat IP..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm">
                                <i class="fas fa-search"></i>
                            </div>

                            <select name="log_action" id="logFilterAction" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm">
                                <option value="">Semua Aksi</option>
                                <option value="ALARM_ON">Sirine Menyala</option>
                                <option value="ALARM_OFF">Sirine Mati Manual</option>
                                <option value="AUTO_OFF">Mati Otomatis</option>
                                <option value="CREATE_USER">Tambah Pengguna</option>
                                <option value="UPDATE_USER">Perbarui Pengguna</option>
                                <option value="DELETE_USER">Hapus Pengguna</option>
                                <option value="CREATE_INCIDENT">Laporan Baru</option>
                                <option value="UPDATE_INCIDENT_STATUS">Perbarui Status Laporan</option>
                                <option value="DELETE_INCIDENT">Hapus Laporan</option>
                                <option value="PANIC_ACTIVATION">Aktivasi Darurat</option>
                            </select>

                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition flex items-center justify-center gap-1">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.riwayat') }}" class="flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg text-sm font-medium transition flex items-center justify-center gap-1">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    {{-- Table Logging --}}
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th class="text-center w-16">No</th>
                                    <th>Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Aksi</th>
                                    <th>Keterangan</th>
                                    <th>Alamat IP</th>
                                    <th class="text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allLogs ?? [] as $log)
                                <tr>
                                    <td class="text-center font-semibold text-gray-700">{{ (($allLogs->currentPage() - 1) * 20) + $loop->iteration }}</td>
                                    <td>
                                        <div>{{ $log->event_time ? $log->event_time->format('H:i:s') : '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->event_time ? $log->event_time->format('d M Y') : '-' }}</div>
                                    </td>
                                    <td class="font-semibold text-gray-800">{{ $log->user_name ?? ($log->user->name ?? 'Sistem') }}</td>
                                    <td>
                                        @php
                                        $badgeClass = match($log->action) {
                                        'ALARM_ON' => 'bg-green-100 text-green-700',
                                        'ALARM_OFF', 'AUTO_OFF' => 'bg-blue-100 text-blue-700',
                                        'CREATE_USER', 'LOGIN' => 'bg-green-100 text-green-700',
                                        'UPDATE_USER', 'UPDATE_INCIDENT_STATUS' => 'bg-yellow-100 text-yellow-700',
                                        'DELETE_USER', 'DELETE_INCIDENT' => 'bg-red-100 text-red-700',
                                        'CREATE_INCIDENT' => 'bg-orange-100 text-orange-700',
                                        'PANIC_ACTIVATION' => 'bg-red-100 text-red-700',
                                        default => 'bg-purple-100 text-purple-700'
                                        };
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                            @php
                                            $actionLabel = match($log->action) {
                                            'ALARM_ON' => 'Sirine Menyala',
                                            'ALARM_OFF' => 'Sirine Mati Manual',
                                            'AUTO_OFF' => 'Mati Otomatis',
                                            'CREATE_USER' => 'Tambah Pengguna',
                                            'UPDATE_USER' => 'Perbarui Pengguna',
                                            'DELETE_USER' => 'Hapus Pengguna',
                                            'LOGIN' => 'Masuk Sistem',
                                            'LOGOUT' => 'Keluar Sistem',
                                            'CREATE_INCIDENT' => 'Laporan Baru',
                                            'UPDATE_INCIDENT_STATUS' => 'Perbarui Status Laporan',
                                            'DELETE_INCIDENT' => 'Hapus Laporan',
                                            'PANIC_ACTIVATION' => 'Aktivasi Darurat',
                                            default => $log->action
                                            };
                                            @endphp
                                            {{ $actionLabel }}
                                        </span>
                                    </td>
                                    <td class="max-w-md"><span class="line-clamp-2">{{ $log->description ?? '-' }}</span></td>
                                    <td class="font-mono">{{ $log->ip_address ?? '-' }}</td>
                                    <td class="text-center">
                                        <button type="button"
                                            data-id="{{ $log->id }}"
                                            data-name="{{ $log->user_name ?? ($log->user->name ?? 'Sistem') }}"
                                            data-action="{{ $log->action }}"
                                            data-action-label="{{ $actionLabel ?? $log->action }}"
                                            data-description="{{ $log->description ?? '-' }}"
                                            data-time="{{ $log->event_time?->format('d M Y H:i:s') ?? '-' }}"
                                            data-ip="{{ $log->ip_address ?? '-' }}"
                                            data-target-type="{{ $log->target_type ?? '-' }}"
                                            data-target-id="{{ $log->target_id ?? '-' }}"
                                            data-old-data="{{ json_encode($log->old_data) }}"
                                            data-new-data="{{ json_encode($log->new_data) }}"
                                            class="view-log-detail-btn text-purple-600 hover:text-purple-700">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-gray-500">
                                        <i class="fas fa-inbox text-3xl mb-2"></i>
                                        <p>Belum ada aktivitas logging</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Logging --}}
                    @if(isset($allLogs) && $allLogs->hasPages())
                    <div class="p-6 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <p class="text-sm text-gray-600">
                            Menampilkan <span class="font-semibold">{{ $allLogs->firstItem() ?? 0 }}-{{ $allLogs->lastItem() ?? 0 }}</span>
                            dari <span class="font-semibold">{{ $allLogs->total() }}</span> log aktivitas
                        </p>
                        <div>
                            {{ $allLogs->appends(request()->query())->links() }}
                        </div>
                    </div>
                    @endif
                </div>

            </main>

        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <div id="detail-modal" class="modal-overlay">
        <div class="modal-content max-w-lg">
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Detail Log Aktivitas</h2>
                <button onclick="closeAllModals()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div><label class="text-xs font-semibold text-gray-500 uppercase">Pengguna</label>
                    <p id="detail-name" class="text-sm text-gray-800 mt-1"></p>
                </div>
                <div><label class="text-xs font-semibold text-gray-500 uppercase">Aksi</label>
                    <p id="detail-action" class="text-sm text-gray-800 mt-1"></p>
                </div>
                <div><label class="text-xs font-semibold text-gray-500 uppercase">Deskripsi</label>
                    <p id="detail-description" class="text-sm text-gray-800 mt-1"></p>
                </div>
                <div><label class="text-xs font-semibold text-gray-500 uppercase">Waktu</label>
                    <p id="detail-time" class="text-sm text-gray-800 mt-1"></p>
                </div>
                <div><label class="text-xs font-semibold text-gray-500 uppercase">Alamat IP</label>
                    <p id="detail-ip" class="text-sm text-gray-800 mt-1"></p>
                </div>
            </div>
            <div class="flex justify-end p-6 border-t border-gray-200">
                <button onclick="closeAllModals()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL LOGGING --}}
    <div id="log-detail-modal" class="modal-overlay">
        <div class="modal-content max-w-2xl">
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Detail Log Aktivitas</h2>
                <button onclick="closeLogDetailModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-xs font-semibold text-gray-500 uppercase">Waktu</label>
                        <p id="log-detail-time" class="text-sm text-gray-800 mt-1"></p>
                    </div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase">Pengguna</label>
                        <p id="log-detail-name" class="text-sm text-gray-800 mt-1"></p>
                    </div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase">Aksi</label>
                        <p id="log-detail-action" class="text-sm text-gray-800 mt-1"></p>
                    </div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase">IP Address</label>
                        <p id="log-detail-ip" class="text-sm text-gray-800 font-mono mt-1"></p>
                    </div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase">Target</label>
                        <p id="log-detail-target" class="text-sm text-gray-800 mt-1"></p>
                    </div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase">Deskripsi</label>
                        <p id="log-detail-description" class="text-sm text-gray-800 mt-1"></p>
                    </div>
                </div>
                <div id="log-detail-changes" class="hidden">
                    <label class="text-xs font-semibold text-gray-500 uppercase">Perubahan Data</label>
                    <div class="mt-2 p-3 bg-gray-50 rounded-lg">
                        <pre id="log-detail-old-data" class="text-xs text-gray-700 whitespace-pre-wrap"></pre>
                        <div class="border-t border-gray-200 my-2"></div>
                        <pre id="log-detail-new-data" class="text-xs text-gray-700 whitespace-pre-wrap"></pre>
                    </div>
                </div>
            </div>
            <div class="flex justify-end p-6 border-t border-gray-200">
                <button onclick="closeLogDetailModal()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg">Tutup</button>
            </div>
        </div>
    </div>

    {{-- EXPORT MODAL --}}
    <div id="exportModal" class="modal-overlay">
        <div class="modal-content max-w-2xl">
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Ekspor Data</h2>
                <button onclick="closeExportModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6 space-y-6">
                <p class="text-gray-700 text-sm">Pilih tabel dan format file untuk mengekspor data:</p>

                {{-- Export Sirine Logs --}}
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-bullhorn text-purple-600"></i>
                        Tabel Aktivitas Sirine
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <button onclick="exportTable('sirine', 'excel')" class="p-3 border-2 border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition text-center group">
                            <i class="fas fa-file-excel text-2xl text-green-600 mb-1"></i>
                            <p class="font-semibold text-gray-800 group-hover:text-green-700 text-sm">Excel</p>
                            <p class="text-xs text-gray-500">.xlsx</p>
                        </button>
                        <button onclick="exportTable('sirine', 'csv')" class="p-3 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-center group">
                            <i class="fas fa-file-csv text-2xl text-blue-600 mb-1"></i>
                            <p class="font-semibold text-gray-800 group-hover:text-blue-700 text-sm">CSV</p>
                            <p class="text-xs text-gray-500">.csv</p>
                        </button>
                        <button onclick="exportTable('sirine', 'pdf')" class="p-3 border-2 border-gray-200 rounded-lg hover:border-red-500 hover:bg-red-50 transition text-center group">
                            <i class="fas fa-file-pdf text-2xl text-red-600 mb-1"></i>
                            <p class="font-semibold text-gray-800 group-hover:text-red-700 text-sm">PDF</p>
                            <p class="text-xs text-gray-500">.pdf</p>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">Kolom: No, Tanggal, Waktu, Pengguna, Jenis Aktivitas, IP Address, Deskripsi</p>
                </div>

                {{-- Export System Logs --}}
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-clipboard-list text-purple-600"></i>
                        Tabel Aktivitas Sistem
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <button onclick="exportTable('system', 'excel')" class="p-3 border-2 border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition text-center group">
                            <i class="fas fa-file-excel text-2xl text-green-600 mb-1"></i>
                            <p class="font-semibold text-gray-800 group-hover:text-green-700 text-sm">Excel</p>
                            <p class="text-xs text-gray-500">.xlsx</p>
                        </button>
                        <button onclick="exportTable('system', 'csv')" class="p-3 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-center group">
                            <i class="fas fa-file-csv text-2xl text-blue-600 mb-1"></i>
                            <p class="font-semibold text-gray-800 group-hover:text-blue-700 text-sm">CSV</p>
                            <p class="text-xs text-gray-500">.csv</p>
                        </button>
                        <button onclick="exportTable('system', 'pdf')" class="p-3 border-2 border-gray-200 rounded-lg hover:border-red-500 hover:bg-red-50 transition text-center group">
                            <i class="fas fa-file-pdf text-2xl text-red-600 mb-1"></i>
                            <p class="font-semibold text-gray-800 group-hover:text-red-700 text-sm">PDF</p>
                            <p class="text-xs text-gray-500">.pdf</p>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">Kolom: No, Tanggal, Waktu, Pengguna, Aksi, Keterangan, IP Address, Target Type, Target ID</p>
                </div>
            </div>
            <div class="flex justify-end p-6 border-t border-gray-200">
                <button onclick="closeExportModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg">Tutup</button>
            </div>
        </div>
    </div>

    {{-- FORM LOGOUT --}}
    <form id="logoutForm" method="POST" action="{{ route('logout') }}" style="display: none;">@csrf</form>

    {{-- Global Scripts --}}
    @include('admin.partials.scripts')

    {{-- JS KHUSUS RIWAYAT --}}
    <script>
        function showLogDetailModal(id, name, action, actionLabel, description, time, ip, targetType, targetId, oldData, newData) {
            document.getElementById('log-detail-time').innerText = time;
            document.getElementById('log-detail-name').innerText = name;
            document.getElementById('log-detail-action').innerHTML = `<span class="px-2 py-1 rounded-full text-xs font-semibold ${getBadgeClass(action)}">${actionLabel}</span>`;
            document.getElementById('log-detail-ip').innerText = ip;
            document.getElementById('log-detail-target').innerText = `${targetType} #${targetId}`;
            document.getElementById('log-detail-description').innerText = description;
            const changesDiv = document.getElementById('log-detail-changes');
            if (oldData && oldData !== 'null' && oldData !== '[]' && JSON.parse(oldData) && Object.keys(JSON.parse(oldData)).length > 0) {
                changesDiv.classList.remove('hidden');
                document.getElementById('log-detail-old-data').innerHTML = '<strong class="text-red-600">Data Lama:</strong>\n' + JSON.stringify(JSON.parse(oldData), null, 2);
                document.getElementById('log-detail-new-data').innerHTML = '<strong class="text-green-600">Data Baru:</strong>\n' + JSON.stringify(JSON.parse(newData), null, 2);
            } else {
                changesDiv.classList.add('hidden');
            }
            document.getElementById('log-detail-modal').classList.add('show');
        }

        function getBadgeClass(action) {
            const classes = {
                'ALARM_ON': 'bg-green-100 text-green-700',
                'ALARM_OFF': 'bg-blue-100 text-blue-700',
                'AUTO_OFF': 'bg-blue-100 text-blue-700',
                'CREATE_USER': 'bg-green-100 text-green-700',
                'UPDATE_USER': 'bg-yellow-100 text-yellow-700',
                'DELETE_USER': 'bg-red-100 text-red-700',
                'CREATE_INCIDENT': 'bg-orange-100 text-orange-700',
                'UPDATE_INCIDENT_STATUS': 'bg-yellow-100 text-yellow-700',
                'DELETE_INCIDENT': 'bg-red-100 text-red-700',
                'PANIC_ACTIVATION': 'bg-red-100 text-red-700'
            };
            return classes[action] || 'bg-purple-100 text-purple-700';
        }

        function closeLogDetailModal() {
            document.getElementById('log-detail-modal').classList.remove('show');
        }

        function openExportModal() {
            document.getElementById('exportModal').classList.add('show');
        }

        function closeExportModal() {
            document.getElementById('exportModal').classList.remove('show');
        }

        function exportTable(tableType, format) {
            closeExportModal();

            let url = `/admin/riwayat/export?format=${format}&export_type=${tableType}`;

            if (tableType === 'sirine') {
                // Add sirine table filters
                if (document.getElementById('searchInput')?.value) {
                    url += `&search=${encodeURIComponent(document.getElementById('searchInput').value)}`;
                }
                if (document.getElementById('filterType')?.value) {
                    url += `&type=${encodeURIComponent(document.getElementById('filterType').value)}`;
                }
            } else {
                // Add system table filters
                if (document.getElementById('logSearchInput')?.value) {
                    url += `&log_search=${encodeURIComponent(document.getElementById('logSearchInput').value)}`;
                }
                if (document.getElementById('logFilterAction')?.value) {
                    url += `&log_action=${encodeURIComponent(document.getElementById('logFilterAction').value)}`;
                }
            }

            window.location.href = url;
        }

        function exportAs(format) {
            // Deprecated - use exportTable instead
            exportTable('sirine', format);
        }

        function exportAs(format) {
            closeExportModal();
            let url = `/admin/riwayat/export?format=${format}`;
            if (document.getElementById('searchInput')?.value) url += `&search=${encodeURIComponent(document.getElementById('searchInput').value)}`;
            if (document.getElementById('filterType')?.value) url += `&type=${encodeURIComponent(document.getElementById('filterType').value)}`;
            window.location.href = url;
        }

        function showDetailModal(id, name, action, description, time, ip) {
            document.getElementById('detail-name').innerText = name;
            document.getElementById('detail-action').innerText = action;
            document.getElementById('detail-description').innerText = description;
            document.getElementById('detail-time').innerText = time;
            document.getElementById('detail-ip').innerText = ip;
            document.getElementById('detail-modal').classList.add('show');
        }

        function closeAllModals() {
            document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('show'));
            document.body.style.overflow = 'auto';
        }

        function filterLogs() {
            document.getElementById('filter-form')?.submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) modal.classList.remove('show');
                });
            });
            document.querySelectorAll('.view-log-detail-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    showLogDetailModal(this.dataset.id, this.dataset.name, this.dataset.action, this.dataset.actionLabel || this.dataset.action, this.dataset.description, this.dataset.time, this.dataset.ip, this.dataset.targetType || '-', this.dataset.targetId || '-', this.dataset.oldData, this.dataset.newData);
                });
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeAllModals();
            });
            document.querySelectorAll('.view-detail-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    showDetailModal(this.dataset.id, this.dataset.name, this.dataset.action, this.dataset.description, this.dataset.time, this.dataset.ip);
                });
            });
            document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterLogs();
                }
            });
        });
    </script>
</body>

</html>