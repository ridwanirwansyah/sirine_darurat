{{-- resources/views/admin/incidents.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sisirine™ - Laporan Kejadian</title>

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Global Styles --}}
    @include('admin.partials.styles')

    {{-- CSS Khusus Laporan Kejadian --}}
    <style>
        /* Status Badges */
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 9999px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-resolved {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-false {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 1rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Table Styles - Responsive */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th {
            background-color: #f1f5f9;
            font-weight: 600;
            text-align: left;
            padding: 1rem;
            font-size: 0.875rem;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.875rem;
        }

        tr:hover {
            background-color: #f8fafc;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Notification Dropdown */
        .notification-dropdown {
            display: none;
            position: absolute;
            right: 0;
            margin-top: 0.5rem;
            width: 20rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            z-index: 50;
        }

        .notification-dropdown.show {
            display: block;
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
            'pageTitle' => 'Laporan Kejadian',
            'pageDescription' => 'Kelola semua laporan kejadian'
            ])

            {{-- MAIN CONTENT --}}
            <main class="main-scroll">

                {{-- STATISTIK SECTION --}}
                <section class="mb-6 md:mb-8">
                    <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Ringkasan Laporan</h3>
                    <div class="stats-grid">
                        {{-- Total Laporan Card --}}
                        <div class="bg-white rounded-xl p-4 md:p-5 shadow hover:shadow-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Total Laporan</p>
                                    <p class="text-2xl md:text-3xl font-bold text-slate-800 mt-2">{{ $totalIncidents ?? 0 }}</p>
                                    <p class="text-xs text-gray-400 mt-1">Semua status</p>
                                </div>
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-alt text-blue-600 text-lg md:text-xl"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Laporan Aktif/Menunggu Card --}}
                        <div class="bg-white rounded-xl p-4 md:p-5 shadow hover:shadow-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Aktif / Menunggu</p>
                                    <p class="text-2xl md:text-3xl font-bold text-yellow-600 mt-2">{{ $activeCount ?? 0 }}</p>
                                    <p class="text-xs text-gray-400 mt-1">Menunggu tindakan</p>
                                </div>
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-clock text-yellow-600 text-lg md:text-xl"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Laporan Selesai Card --}}
                        <div class="bg-white rounded-xl p-4 md:p-5 shadow hover:shadow-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Selesai</p>
                                    <p class="text-2xl md:text-3xl font-bold text-green-600 mt-2">{{ $resolvedCount ?? 0 }}</p>
                                    <p class="text-xs text-gray-400 mt-1">Sudah ditangani</p>
                                </div>
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-check-circle text-green-600 text-lg md:text-xl"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Alarm Palsu Card --}}
                        <div class="bg-white rounded-xl p-4 md:p-5 shadow hover:shadow-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Alarm Palsu</p>
                                    <p class="text-2xl md:text-3xl font-bold text-red-600 mt-2">{{ $falseAlarmCount ?? 0 }}</p>
                                    <p class="text-xs text-gray-400 mt-1">Laporan palsu</p>
                                </div>
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-times-circle text-red-600 text-lg md:text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- FILTER & SEARCH SECTION --}}
                <section class="mb-6">
                    <div class="bg-white rounded-xl shadow-md p-4 md:p-6 border border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                            <h3 class="text-base md:text-lg font-bold text-gray-800 flex items-center">
                                <i class="fas fa-filter mr-3 text-blue-600"></i>
                                Filter & Pencarian
                            </h3>

                            {{-- EXPORT BUTTONS --}}
                            <div class="flex gap-2">
                                <a href="{{ route('admin.incidents.export.csv') }}?{{ http_build_query(request()->except('page')) }}" class="flex items-center gap-1 md:gap-2 px-3 md:px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs md:text-sm font-medium transition">
                                    <i class="fas fa-file-csv"></i>
                                    <span class="hidden sm:inline">Ekspor CSV</span>
                                </a>
                                <a href="{{ route('admin.incidents.export.excel') }}?{{ http_build_query(request()->except('page')) }}" class="flex items-center gap-1 md:gap-2 px-3 md:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs md:text-sm font-medium transition">
                                    <i class="fas fa-file-excel"></i>
                                    <span class="hidden sm:inline">Ekspor Excel</span>
                                </a>
                            </div>
                        </div>

                        <form id="filterForm" action="{{ route('admin.incidents.index') }}" method="GET">
                            @csrf

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                {{-- Filter Status --}}
                                <div>
                                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        <option value="all" {{ ($status ?? 'all') == 'all' ? 'selected' : '' }}>Semua Status</option>
                                        <option value="ACTIVE" {{ ($status ?? '') == 'ACTIVE' ? 'selected' : '' }}>Aktif / Menunggu</option>
                                        <option value="RESOLVED" {{ ($status ?? '') == 'RESOLVED' ? 'selected' : '' }}>Selesai</option>
                                        <option value="FALSE_ALARM" {{ ($status ?? '') == 'FALSE_ALARM' ? 'selected' : '' }}>Alarm Palsu</option>
                                    </select>
                                </div>

                                {{-- Filter Jenis Kejadian --}}
                                <div>
                                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Jenis Kejadian</label>
                                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        <option value="all" {{ ($type ?? 'all') == 'all' ? 'selected' : '' }}>Semua Jenis</option>
                                        @foreach($typeOptions ?? [] as $key => $label)
                                        <option value="{{ $key }}" {{ ($type ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Date Range --}}
                                <div>
                                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                                    <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                </div>

                                <div>
                                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                                    <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                </div>
                            </div>

                            {{-- Search Bar --}}
                            <div class="mt-4">
                                <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Cari Pengguna / Lokasi / Deskripsi</label>
                                <div class="relative">
                                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari berdasarkan nama, lokasi, atau deskripsi..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                </div>
                            </div>

                            {{-- Filter Buttons --}}
                            <div class="flex gap-3 mt-4">
                                <button type="submit" class="px-4 md:px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                                    <i class="fas fa-search mr-2"></i>Terapkan Filter
                                </button>
                                <a href="{{ route('admin.incidents.index') }}" class="px-4 md:px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                                    <i class="fas fa-redo mr-2"></i>Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- TABLE SECTION --}}
                <section>
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="p-4 md:p-6 border-b border-gray-200">
                            <h3 class="text-base md:text-lg font-bold text-gray-800 flex items-center">
                                <i class="fas fa-table mr-3 text-blue-600"></i>
                                Daftar Laporan Kejadian
                            </h3>
                        </div>

                        <div class="table-container">
                            <table class="w-full">
                                <thead>
                                    <tr>
                                        <th class="text-center w-16">ID</th>
                                        <th>Nama Pengguna</th>
                                        <th>Jenis Kejadian</th>
                                        <th>Lokasi</th>
                                        <th class="text-center">Status</th>
                                        <th>Waktu Laporan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($incidents ?? [] as $incident)
                                    <tr>
                                        <td class="text-center font-semibold text-gray-700">#{{ str_pad($incident->id, 3, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-semibold">
                                                    {{ substr($incident->user->name ?? 'NA', 0, 2) }}
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-800">{{ $incident->user->name ?? 'Tidak Diketahui' }}</p>
                                                    <p class="text-xs text-gray-500">Warga</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center gap-1 px-2 py-1 {{ $incident->getTypeBadgeClass() }} rounded text-xs font-medium">
                                                <i class="fas {{ $incident->getTypeIcon() }}"></i>
                                                {{ $incident->getTypeLabel() }}
                                            </span>
                                        </td>
                                        <td class="text-gray-700 max-w-[200px] truncate" title="{{ $incident->location }}">{{ $incident->location }}</td>
                                        <td class="text-center">
                                            @php
                                            $statusLabels = [
                                            'ACTIVE' => 'Aktif',
                                            'RESOLVED' => 'Selesai',
                                            'FALSE_ALARM' => 'Alarm Palsu'
                                            ];
                                            $statusLabel = $statusLabels[$incident->status] ?? $incident->status;
                                            $statusBadgeClass = match($incident->status) {
                                            'ACTIVE' => 'status-pending',
                                            'RESOLVED' => 'status-resolved',
                                            'FALSE_ALARM' => 'status-false',
                                            default => 'status-pending'
                                            };
                                            $statusIcon = match($incident->status) {
                                            'ACTIVE' => 'fa-clock',
                                            'RESOLVED' => 'fa-check-circle',
                                            'FALSE_ALARM' => 'fa-times-circle',
                                            default => 'fa-clock'
                                            };
                                            @endphp
                                            <span class="status-badge {{ $statusBadgeClass }}">
                                                <i class="fas {{ $statusIcon }}"></i>
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="text-gray-600 text-xs whitespace-nowrap">{{ $incident->reported_at?->format('d M Y, H:i') }}</td>
                                        <td>
                                            <div class="flex items-center justify-center gap-2">
                                                <button onclick="openDetailModal('{{ $incident->id }}')" class="px-2 md:px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded text-xs font-medium transition">
                                                    <i class="fas fa-eye mr-1"></i>Detail
                                                </button>
                                                <button onclick="openUpdateModal('{{ $incident->id }}')" class="px-2 md:px-3 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded text-xs font-medium transition">
                                                    <i class="fas fa-edit mr-1"></i>Ubah
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-8 text-gray-500">
                                            <i class="fas fa-inbox text-3xl mb-2"></i>
                                            <p>Tidak ada laporan kejadian ditemukan</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINATION --}}
                        @if(isset($incidents) && $incidents->hasPages())
                        <div class="p-4 md:p-6 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <p class="text-xs md:text-sm text-gray-600">
                                Menampilkan
                                <span class="font-semibold">{{ $incidents->firstItem() ?? 0 }}-{{ $incidents->lastItem() ?? 0 }}</span>
                                dari <span class="font-semibold">{{ $incidents->total() }}</span> laporan
                            </p>
                            <div class="flex gap-2 flex-wrap">
                                {{ $incidents->links() }}
                            </div>
                        </div>
                        @endif
                    </div>
                </section>
            </main>
        </div>
    </div>

    {{-- MODAL DETAIL LAPORAN --}}
    <div id="detailModal" class="modal">
        <div class="modal-content">
            {{-- Modal content akan diisi via JavaScript --}}
        </div>
    </div>

    {{-- MODAL UBAH STATUS --}}
    <div id="updateModal" class="modal">
        <div class="modal-content">
            {{-- Modal content akan diisi via JavaScript --}}
        </div>
    </div>

    {{-- LIGHTBOX IMAGE VIEWER --}}
    <div id="imageLightbox" class="hidden fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-[200]">
        <button onclick="closeLightbox()" class="absolute top-5 right-5 text-white text-3xl hover:text-gray-300 transition">
            <i class="fas fa-times"></i>
        </button>
        <img id="lightboxImage" class="max-w-[90%] max-h-[85%] rounded-lg shadow-lg">
    </div>

    {{-- LOADING SPINNER --}}
    <div id="loadingSpinner" class="hidden fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg flex items-center space-x-3">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Memproses...</span>
        </div>
    </div>

    {{-- Global Scripts --}}
    @include('admin.partials.scripts')

    {{-- JS KHUSUS LAPORAN KEJADIAN --}}
    <script>
        // CSRF Token untuk AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        /**
         * Show loading spinner
         */
        function showLoading() {
            document.getElementById('loadingSpinner').classList.remove('hidden');
        }

        /**
         * Hide loading spinner
         */
        function hideLoading() {
            document.getElementById('loadingSpinner').classList.add('hidden');
        }

        /**
         * Helper functions untuk frontend (Bahasa Indonesia)
         */
        function getTypeBadgeClass(type) {
            const classes = {
                'KEBAKARAN': 'bg-red-50 text-red-700',
                'PENCURIAN': 'bg-purple-50 text-purple-700',
                'GEMPA_BUMI': 'bg-orange-50 text-orange-700',
                'BANJIR': 'bg-blue-50 text-blue-700',
                'KECELAKAAN': 'bg-yellow-50 text-yellow-700',
                'PENYERANGAN': 'bg-red-100 text-red-800',
                'GANGGUAN_KEAMANAN': 'bg-indigo-50 text-indigo-700',
                'LAINNYA': 'bg-gray-50 text-gray-700'
            };
            return classes[type] || 'bg-gray-50 text-gray-700';
        }

        function getTypeIcon(type) {
            const icons = {
                'KEBAKARAN': 'fa-fire',
                'PENCURIAN': 'fa-user-secret',
                'GEMPA_BUMI': 'fa-mountain',
                'BANJIR': 'fa-water',
                'KECELAKAAN': 'fa-car-crash',
                'PENYERANGAN': 'fa-fist-raised',
                'GANGGUAN_KEAMANAN': 'fa-shield-alt',
                'LAINNYA': 'fa-ellipsis-h'
            };
            return icons[type] || 'fa-exclamation-triangle';
        }

        function getStatusBadgeClass(status) {
            const classes = {
                'ACTIVE': 'status-pending',
                'RESOLVED': 'status-resolved',
                'FALSE_ALARM': 'status-false'
            };
            return classes[status] || 'status-pending';
        }

        function getStatusLabel(status) {
            const labels = {
                'ACTIVE': 'Aktif / Menunggu',
                'RESOLVED': 'Selesai',
                'FALSE_ALARM': 'Alarm Palsu'
            };
            return labels[status] || status;
        }

        function getStatusIcon(status) {
            const icons = {
                'ACTIVE': 'fa-clock',
                'RESOLVED': 'fa-check-circle',
                'FALSE_ALARM': 'fa-times-circle'
            };
            return icons[status] || 'fa-clock';
        }

        /**
         * Open detail modal
         */
        async function openDetailModal(id) {
            showLoading();
            try {
                const response = await fetch(`/admin/incidents/${id}`);
                const data = await response.json();

                if (data.success) {
                    const incident = data.data;
                    const images = data.image_urls || [];

                    const modalContent = `
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 rounded-t-xl">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xl font-bold text-white flex items-center">
                                    <i class="fas fa-file-alt mr-3"></i>
                                    Detail Laporan Kejadian
                                </h3>
                                <button onclick="closeDetailModal()" class="text-white hover:text-gray-200 transition">
                                    <i class="fas fa-times text-2xl"></i>
                                </button>
                            </div>
                        </div>

                        <div class="p-6 space-y-5">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase">ID Laporan</p>
                                    <p class="text-lg font-bold text-gray-800">#${String(incident.id).padStart(3, '0')}</p>
                                </div>
                                <span class="status-badge ${getStatusBadgeClass(incident.status)}">
                                    <i class="fas ${getStatusIcon(incident.status)}"></i>
                                    ${data.status_label}
                                </span>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-3">Pelapor</p>
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-12 h-12 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-semibold">
                                        ${(incident.user?.name || 'NA').substring(0, 2).toUpperCase()}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">${incident.user?.name || 'N/A'}</p>
                                        <p class="text-sm text-gray-500">Warga • ID: ${incident.user_id}</p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            <i class="fas fa-envelope mr-1"></i>${incident.user?.email || 'N/A'}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Jenis Kejadian</p>
                                    <span class="inline-flex items-center gap-1 px-3 py-1 ${getTypeBadgeClass(incident.type)} rounded font-medium">
                                        <i class="fas ${getTypeIcon(incident.type)}"></i>
                                        ${data.type_label}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Lokasi</p>
                                    <p class="text-sm font-medium text-gray-800">${incident.location || '-'}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Dilaporkan Pada</p>
                                    <p class="text-sm font-medium text-gray-800">
                                        <i class="far fa-calendar-alt mr-2 text-blue-600"></i>
                                        ${data.reported_at_formatted}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Diselesaikan Pada</p>
                                    <p class="text-sm font-medium ${incident.resolved_at ? 'text-gray-800' : 'text-gray-400'}">
                                        <i class="far fa-calendar-alt mr-2"></i>
                                        ${data.resolved_at_formatted}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Deskripsi Kejadian</p>
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">
                                        ${incident.description || 'Tidak ada deskripsi'}
                                    </p>
                                </div>
                            </div>

                            ${images.length > 0 ? `
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Foto Kejadian</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    ${images.map(img => `
                                        <div class="rounded-lg overflow-hidden border">
                                            <img src="${img}" 
                                                class="w-full h-32 object-cover cursor-pointer hover:scale-105 transition"
                                                onclick="openLightbox('${img}')">
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                            ` : `
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Foto Kejadian</p>
                                <p class="text-sm text-gray-400">Tidak ada foto</p>
                            </div>
                            `}

                            ${incident.resolution_notes ? `
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Catatan Resolusi</p>
                                <div class="p-4 bg-green-50 rounded-lg border border-green-100">
                                    <p class="text-sm text-green-700 leading-relaxed whitespace-pre-wrap">
                                        ${incident.resolution_notes}
                                    </p>
                                </div>
                            </div>
                            ` : ''}
                        </div>

                        <div class="p-6 border-t border-gray-200">
                            <button onclick="closeDetailModal()" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                                Tutup
                            </button>
                        </div>
                    `;

                    document.getElementById('detailModal').querySelector('.modal-content').innerHTML = modalContent;
                    document.getElementById('detailModal').classList.add('active');
                    document.body.style.overflow = 'hidden';
                } else {
                    alert(data.message || 'Gagal memuat detail laporan');
                }
            } catch (error) {
                console.error('Error loading incident details:', error);
                alert('Gagal memuat detail laporan');
            } finally {
                hideLoading();
            }
        }

        /**
         * Open update modal
         */
        async function openUpdateModal(id) {
            showLoading();
            try {
                const response = await fetch(`/admin/incidents/${id}`);
                const data = await response.json();

                if (data.success) {
                    const incident = data.data;

                    const modalContent = `
                        <div class="bg-gradient-to-r from-green-600 to-green-700 p-6 rounded-t-xl">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xl font-bold text-white flex items-center">
                                    <i class="fas fa-edit mr-3"></i>
                                    Ubah Status Laporan
                                </h3>
                                <button onclick="closeUpdateModal()" class="text-white hover:text-gray-200 transition">
                                    <i class="fas fa-times text-2xl"></i>
                                </button>
                            </div>
                        </div>

                        <form id="updateStatusForm" onsubmit="updateIncidentStatus(event, ${id})">
                            <div class="p-6 space-y-5">
                                <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                                    <div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase">ID Laporan</p>
                                        <p class="text-lg font-bold text-gray-800">#${String(incident.id).padStart(3, '0')}</p>
                                    </div>
                                    <span class="status-badge ${getStatusBadgeClass(incident.status)}">
                                        <i class="fas ${getStatusIcon(incident.status)}"></i>
                                        ${getStatusLabel(incident.status)}
                                    </span>
                                </div>

                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Ubah Status</p>
                                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="ACTIVE" ${incident.status === 'ACTIVE' ? 'selected' : ''}>Aktif / Menunggu</option>
                                        <option value="RESOLVED" ${incident.status === 'RESOLVED' ? 'selected' : ''}>Selesai</option>
                                        <option value="FALSE_ALARM" ${incident.status === 'FALSE_ALARM' ? 'selected' : ''}>Alarm Palsu</option>
                                    </select>
                                </div>

                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Catatan Penyelesaian</p>
                                    <textarea name="notes" placeholder="Masukkan catatan penyelesaian (opsional)..." rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none">${incident.resolution_notes || ''}</textarea>
                                </div>
                            </div>

                            <div class="p-6 border-t border-gray-200 flex gap-3">
                                <button type="button" onclick="closeUpdateModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition">
                                    Batal
                                </button>
                                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    `;

                    document.getElementById('updateModal').querySelector('.modal-content').innerHTML = modalContent;
                    document.getElementById('updateModal').classList.add('active');
                    document.body.style.overflow = 'hidden';
                } else {
                    alert(data.message || 'Gagal memuat data laporan');
                }
            } catch (error) {
                console.error('Error loading incident for update:', error);
                alert('Gagal memuat data laporan');
            } finally {
                hideLoading();
            }
        }

        /**
         * Update incident status via AJAX
         */
        async function updateIncidentStatus(event, id) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);

            showLoading();

            try {
                const response = await fetch(`/admin/incidents/${id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: formData.get('status'),
                        notes: formData.get('notes')
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Status laporan berhasil diperbarui');
                    closeUpdateModal();
                    location.reload();
                } else {
                    alert('Gagal memperbarui status: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (error) {
                console.error('Error updating incident:', error);
                alert('Gagal memperbarui status laporan');
            } finally {
                hideLoading();
            }
        }

        /**
         * Close detail modal
         */
        function closeDetailModal() {
            document.getElementById('detailModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        /**
         * Close update modal
         */
        function closeUpdateModal() {
            document.getElementById('updateModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        /**
         * Open lightbox
         */
        function openLightbox(imageUrl) {
            const lightbox = document.getElementById('imageLightbox');
            const img = document.getElementById('lightboxImage');
            img.src = imageUrl;
            lightbox.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        /**
         * Close lightbox
         */
        function closeLightbox() {
            const lightbox = document.getElementById('imageLightbox');
            lightbox.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        /**
         * Go to incident from notification
         */
        function goToIncident(button) {
            const id = button.getAttribute('data-id');
            if (id) {
                openDetailModal(id);
            }
        }

        /**
         * Close modal when clicking outside
         */
        window.addEventListener('click', (e) => {
            const detailModal = document.getElementById('detailModal');
            const updateModal = document.getElementById('updateModal');

            if (e.target === detailModal) {
                closeDetailModal();
            }
            if (e.target === updateModal) {
                closeUpdateModal();
            }
        });

        /**
         * Close modal with ESC key
         */
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const lightbox = document.getElementById('imageLightbox');
                const detailModal = document.getElementById('detailModal');
                const updateModal = document.getElementById('updateModal');

                if (!lightbox.classList.contains('hidden')) {
                    closeLightbox();
                } else if (detailModal.classList.contains('active')) {
                    closeDetailModal();
                } else if (updateModal.classList.contains('active')) {
                    closeUpdateModal();
                }
            }
        });

        // Auto-submit filter form when select changes
        document.querySelectorAll('#filterForm select').forEach(select => {
            select.addEventListener('change', () => {
                document.getElementById('filterForm').submit();
            });
        });

        // Inisialisasi saat DOM siap
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Laporan Kejadian page loaded');
        });
    </script>
</body>

</html>