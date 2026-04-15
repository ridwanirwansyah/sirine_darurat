{{-- resources/views/admin/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sisirine™ - Admin Dashboard</title>

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- ChartJS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- MQTT Client --}}
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

    {{-- Global Styles --}}
    @include('admin.partials.styles')

    {{-- CSS Khusus Dashboard --}}
    <style>
        /* Notification Badge */
        .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #ef4444;
        }

        /* Status Badges */
        .status-badge {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 9999px;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-processed {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-completed {
            background-color: #dbeafe;
            color: #1e40af;
        }

        /* Chart Container */
        .chart-container {
            height: 300px;
            width: 100%;
        }

        /* Animations */
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(-10deg);
            }

            50% {
                transform: rotate(0deg);
            }

            75% {
                transform: rotate(10deg);
            }
        }

        @keyframes slideInDown {
            from {
                transform: translate(-50%, -100%);
                opacity: 0;
            }

            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .animate-pulse {
            animation: pulse 1.5s infinite;
        }

        .fa-shake {
            animation: shake 0.5s ease-in-out infinite;
        }

        /* Manual Play Button Animation */
        @keyframes manualPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
                transform: scale(1);
            }

            70% {
                box-shadow: 0 0 0 15px rgba(239, 68, 68, 0);
                transform: scale(1.05);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
                transform: scale(1);
            }
        }

        /* Emergency Banner */
        .emergency-banner {
            animation: slideInDown 0.3s ease-out;
        }

        /* Toast Notification */
        .toast-notification {
            animation: slideInRight 0.3s ease-out;
        }

        /* Manual Play Button */
        #manualPlayBtn button {
            animation: manualPulse 2s infinite;
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
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

    {{-- SIRINE ALERT BANNER --}}
    <div id="sirenAlertBanner"
        class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md emergency-banner">
        <div class="bg-red-600 text-white p-4 rounded-lg shadow-xl animate-pulse border-2 border-red-300">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-white p-2 rounded-full">
                        <i class="fas fa-bullhorn text-red-600 text-xl fa-shake"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">SIRINE AKTIF!</h3>
                        <p class="text-sm opacity-90">Ada insiden darurat yang sedang terjadi</p>
                    </div>
                </div>
                <button onclick="dismissSirenAlert()" class="text-white hover:text-red-200 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-3 flex gap-2">
                <button onclick="location.href='/admin/incidents'" class="flex-1 bg-white text-red-600 font-bold py-2 rounded-lg hover:bg-red-50 transition">
                    <i class="fas fa-eye mr-2"></i>Lihat Insiden
                </button>
                <button onclick="silenceSirenAudio()" class="flex-1 bg-red-800 text-white font-bold py-2 rounded-lg hover:bg-red-900 transition">
                    <i class="fas fa-volume-mute mr-2"></i>Diamkan Suara
                </button>
            </div>
        </div>
    </div>

    {{-- TOAST NOTIFICATION --}}
    <div id="toastNotification"
        class="hidden fixed bottom-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg z-50 max-w-xs toast-notification">
        <div class="flex items-start gap-3">
            <i class="fas fa-bell text-xl mt-0.5"></i>
            <div>
                <p class="font-bold">Notifikasi Baru</p>
                <p id="toastMessage" class="text-sm opacity-90"></p>
            </div>
        </div>
    </div>

    {{-- SIRINE STATUS MINI BADGE --}}
    <div id="sirenStatusMini" class="fixed bottom-6 right-6 z-40">
        <div id="miniSirenBadge" class="bg-red-600 text-white px-3 py-2 rounded-full shadow-lg animate-pulse hidden">
            <i class="fas fa-bullhorn mr-2"></i>
            <span class="font-bold">SIRINE AKTIF</span>
        </div>
    </div>

    {{-- MANUAL PLAY BUTTON --}}
    <div id="manualPlayBtn" class="hidden fixed bottom-24 right-6 z-50">
        <button onclick="enableSirenAudio()"
            class="bg-red-600 hover:bg-red-700 text-white px-5 py-3 rounded-full shadow-lg flex items-center gap-3 transition-all">
            <i class="fas fa-volume-up text-xl"></i>
            <div class="text-left">
                <div class="font-bold text-sm">AKTIFKAN SUARA SIRINE</div>
                <div class="text-xs opacity-80">Klik untuk mendengarkan peringatan</div>
            </div>
        </button>
    </div>

    <div class="flex min-h-screen">
        {{-- SIDEBAR --}}
        @include('admin.partials.sidebar')

        {{-- MAIN CONTENT --}}
        <div class="flex-1 flex flex-col dashboard-content">

            {{-- HEADER --}}
            @include('admin.partials.header', [
            'pageTitle' => 'Dashboard',
            'pageDescription' => 'Overview sistem dan aktivitas terbaru'
            ])

            {{-- MAIN CONTENT --}}
            <main class="flex-1 p-6 overflow-auto">

                {{-- STATISTIK SECTION --}}
                <section class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Sistem</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                        {{-- Total Users Card (Hanya role 'user') --}}
                        <div class="bg-white rounded-xl p-5 shadow hover:shadow-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Total User</p>
                                    <p class="text-3xl font-bold text-slate-800 mt-2">{{ $totalUsers ?? 0 }}</p>
                                    <p class="text-xs text-gray-400 mt-1">Akun dengan role User</p>
                                </div>
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-users text-blue-600 text-xl"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Sirine Dinyalakan Card (ALARM_ON) --}}
                        <div class="bg-white rounded-xl p-5 shadow hover:shadow-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Sirine Dinyalakan</p>
                                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $totalAlarmOn ?? 0 }}</p>
                                    <p class="text-xs text-gray-400 mt-1">Total aktivasi sirine</p>
                                </div>
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-volume-up text-green-600 text-xl"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Sirine Mati Otomatis Card (AUTO_OFF) --}}
                        <div class="bg-white rounded-xl p-5 shadow hover:shadow-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Sirine Mati Otomatis</p>
                                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ $totalAlarmOff ?? 0 }}</p>
                                    <p class="text-xs text-gray-400 mt-1">Total auto-off sirine</p>
                                </div>
                                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                {{-- SISTEM STATUS SECTION --}}
                <section class="mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-server mr-3 text-blue-600"></i>
                            Status Sistem
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- MQTT Status --}}
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-600">MQTT Connection</span>
                                    <div id="mqttStatus" class="flex items-center">
                                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></span>
                                        <span class="text-xs font-medium text-red-600">Connecting...</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">Broker: projekiot/lampu/kendali</p>
                            </div>

                            {{-- Sirine Status --}}
                            <div id="sirenStatusCard" class="p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-600">Status Sirine</span>
                                    <div id="sirenStatusIndicator" class="flex items-center">
                                        <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>
                                        <span class="text-xs font-medium text-gray-600">OFFLINE</span>
                                    </div>
                                </div>
                                <p id="sirenStatusText" class="text-xs text-gray-500">Menunggu status dari MQTT...</p>
                            </div>

                            {{-- Last Update --}}
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-600">Terakhir Update</span>
                                    <i class="fas fa-sync-alt text-gray-400 text-sm"></i>
                                </div>
                                <p id="lastUpdateTime" class="text-xs text-gray-500">--:--:--</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- AKTIVITAS TERAKHIR SECTION (ALARM_ON dan AUTO_OFF) --}}
                <section class="mt-8">
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                        <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-history mr-3 text-blue-600"></i>
                            Aktivitas Terakhir
                        </h2>

                        <div class="divide-y divide-gray-200">
                            @forelse($recentLogs as $log)
                            <div class="flex items-center justify-between py-4 hover:bg-gray-50 px-2 rounded transition">
                                <div class="flex items-center gap-4">
                                    @php
                                    $colors = [
                                    'bg-blue-100 text-blue-700',
                                    'bg-green-100 text-green-700',
                                    'bg-purple-100 text-purple-700',
                                    'bg-pink-100 text-pink-700',
                                    'bg-yellow-100 text-yellow-700'
                                    ];
                                    $color = $colors[$log->user_id % count($colors)];
                                    @endphp

                                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $color }} flex-shrink-0">
                                        <i class="fas fa-user text-sm"></i>
                                    </div>

                                    <div>
                                        <p class="font-semibold text-sm text-gray-800">
                                            {{ $log->user->name ?? 'System' }}
                                            <span class="text-gray-500 text-xs ml-2">{{ ucfirst(strtolower($log->user->role ?? 'AUTO')) }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ \Carbon\Carbon::parse($log->event_time)->translatedFormat('d M Y H:i:s') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="text-right">
                                    @if($log->action === 'ALARM_ON')
                                    <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                        <i class="fas fa-volume-up mr-1"></i>Menyalakan Sirine
                                    </span>
                                    @elseif($log->action === 'AUTO_OFF')
                                    <span class="inline-block px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">
                                        <i class="fas fa-clock mr-1"></i>Auto-Off Sirine
                                    </span>
                                    @else
                                    <span class="inline-block px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">
                                        <i class="fas fa-robot mr-1"></i>{{ $log->action }}
                                    </span>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1">Log #{{ $log->id }}</p>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8">
                                <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                                <p class="text-gray-500 text-sm">Belum ada aktivitas</p>
                            </div>
                            @endforelse
                        </div>

                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <a href="/admin/riwayat" class="text-blue-600 hover:text-blue-700 font-semibold text-sm flex items-center justify-center py-2 transition">
                                Lihat Semua Aktivitas
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </section>

                {{-- LAPORAN TERAKHIR SECTION --}}
                <section class="mt-8">
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                        <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-3 text-red-600"></i>
                            5 Laporan Terakhir
                        </h2>

                        <div class="divide-y divide-gray-200">
                            @foreach($recentIncidents as $incident)
                            <div class="flex items-center justify-between py-4 hover:bg-gray-50 px-2 rounded cursor-pointer transition"
                                data-incident='{{ json_encode($incident, JSON_HEX_APOS | JSON_HEX_TAG) }}'
                                onclick='openIncidentModal(JSON.parse(this.dataset.incident))'>
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center font-bold">
                                        #{{ $incident->id }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-sm text-gray-800">
                                            {{ $incident->user->name ?? 'Unknown' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $incident->type }} • {{ $incident->location }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($incident->status === 'ACTIVE')
                                    <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">
                                        Active
                                    </span>
                                    @elseif($incident->status === 'RESOLVED')
                                    <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                        Selesai
                                    </span>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ \Carbon\Carbon::parse($incident->reported_at)->format('d M Y H:i') }}
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="pt-4 mt-4 border-t text-center">
                            <a href="/admin/incidents" class="text-red-600 hover:text-red-700 text-sm font-semibold">
                                Lihat Semua →
                            </a>
                        </div>
                    </div>
                </section>
            </main>

        </div>
    </div>

    {{-- INCIDENT MODAL --}}
    <div id="incidentModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 relative shadow-xl animate-fadeIn">
            <button onclick="closeIncidentModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-lg">
                <i class="fas fa-times"></i>
            </button>
            <h2 class="text-lg font-bold text-gray-800 mb-5 flex items-center">
                <i class="fas fa-file-alt mr-2 text-red-500"></i>
                Detail Laporan
            </h2>
            <div class="space-y-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">ID</span>
                    <span id="m_id" class="font-semibold"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">User</span>
                    <span id="m_user"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Jenis</span>
                    <span id="m_type"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Lokasi</span>
                    <span id="m_location"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Status</span>
                    <span id="m_status_badge"></span>
                </div>
                <div>
                    <span class="text-gray-500 block mb-1">Deskripsi</span>
                    <p id="m_desc" class="bg-gray-50 p-3 rounded-lg text-gray-700"></p>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Dilaporkan</span>
                    <span id="m_reported"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Diselesaikan</span>
                    <span id="m_resolved"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Global Scripts --}}
    @include('admin.partials.scripts')

    {{-- JS KHUSUS DASHBOARD --}}
    <script>
        // ==============================
        // GLOBAL VARIABLES
        // ==============================
        let isSirenActive = false;
        let sirenNotificationShown = false;
        let notificationCount = 0;
        let userInteracted = false;
        let sirenAudio = null;
        let notificationAudio = null;

        // MQTT Configuration
        const mqttOptions = {
            username: 'lampu_control',
            password: 'Lampu_control123!',
            clientId: 'AdminDashboard_' + Math.random().toString(16).substr(2, 8),
            clean: true,
            reconnectPeriod: 5000,
            connectTimeout: 10000
        };

        const broker = 'wss://4037529a78a04d66ad4be0089551ad91.s1.eu.hivemq.cloud:8884/mqtt';
        const topicAlarm = 'projekiot/lampu/kendali';

        let client = null;

        // ==============================
        // SIMPLE AUDIO SYSTEM (LOKAL FILE)
        // ==============================

        function initializeAudioSystem() {
            console.log('Initializing audio system with local files...');
            try {
                sirenAudio = new Audio();
                notificationAudio = new Audio();

                // Konfigurasi audio sirine
                sirenAudio.loop = true;
                sirenAudio.volume = 0.5;

                // Gunakan file audio lokal dari folder public/audio/
                // Pastikan file sudah ditempatkan di public/audio/sirine.wav
                sirenAudio.src = '/audio/sirine.wav';
                notificationAudio.src = '/audio/notification.wav';

                // Preload audio untuk mempercepat respons
                sirenAudio.load();
                notificationAudio.load();

                console.log('Audio system initialized with local files');
                console.log('Siren audio source:', sirenAudio.src);
                console.log('Notification audio source:', notificationAudio.src);
            } catch (error) {
                console.error('Failed to initialize audio:', error);
            }
        }

        /**
         * Test audio (untuk debugging)
         */
        function testSirenAudio() {
            if (sirenAudio) {
                sirenAudio.currentTime = 0;
                sirenAudio.play().catch(e => console.error('Test play failed:', e));
                setTimeout(() => {
                    sirenAudio.pause();
                    sirenAudio.currentTime = 0;
                }, 3000);
            }
        }

        // ==============================
        // AUDIO CONTROL FUNCTIONS
        // ==============================

        function playSirenSound() {
            if (!isSirenActive) {
                console.log('Cannot play siren: siren is not active');
                return;
            }
            if (!sirenAudio) {
                console.log('Siren audio not initialized');
                initializeAudioSystem();
                setTimeout(playSirenSound, 500);
                return;
            }
            if (!userInteracted) {
                console.log('User has not interacted yet, showing manual play button');
                showManualPlayButton();
                return;
            }
            try {
                // Reset audio ke awal
                sirenAudio.currentTime = 0;
                const playPromise = sirenAudio.play();
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        console.log('Siren sound started successfully');
                        hideManualPlayButton();
                    }).catch(error => {
                        console.warn('Failed to play siren:', error);
                        showManualPlayButton();
                    });
                }
            } catch (error) {
                console.error('Error playing siren:', error);
                showManualPlayButton();
            }
        }

        function stopSirenSound() {
            if (sirenAudio) {
                try {
                    sirenAudio.pause();
                    sirenAudio.currentTime = 0;
                    console.log('Siren sound stopped');
                } catch (error) {
                    console.error('Error stopping siren:', error);
                }
            }
        }

        function silenceSirenAudio() {
            if (sirenAudio) {
                try {
                    sirenAudio.volume = 0;
                    console.log('Siren sound muted');
                    showToast('Suara sirine telah didiamkan');
                } catch (error) {
                    console.error('Error muting siren:', error);
                }
            }
        }

        function playNotificationSound() {
            if (!notificationAudio) {
                console.log('Notification audio not initialized');
                return;
            }
            if (!userInteracted) {
                console.log('Skipping notification sound - user not interacted yet');
                return;
            }
            try {
                notificationAudio.currentTime = 0;
                const playPromise = notificationAudio.play();
                if (playPromise !== undefined) {
                    playPromise.catch(error => {
                        console.log('Notification sound blocked (normal):', error.message);
                    });
                }
            } catch (error) {
                console.error('Error playing notification:', error);
            }
        }

        // ==============================
        // INCIDENT MODAL FUNCTIONS
        // ==============================

        function openIncidentModal(incident) {
            const modal = document.getElementById('incidentModal');
            if (!modal) return;
            document.getElementById('m_id').innerText = '#' + incident.id;
            document.getElementById('m_user').innerText = incident.user?.name ?? 'Unknown';
            document.getElementById('m_type').innerText = incident.type;
            document.getElementById('m_location').innerText = incident.location;
            document.getElementById('m_desc').innerText = incident.description ?? '-';
            document.getElementById('m_reported').innerText = incident.reported_at;
            document.getElementById('m_resolved').innerText = incident.resolved_at ?? '-';
            let badge = incident.status === 'ACTIVE' ? '<span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">Active</span>' :
                incident.status === 'RESOLVED' ? '<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Selesai</span>' :
                '<span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">' + incident.status + '</span>';
            document.getElementById('m_status_badge').innerHTML = badge;
            modal.classList.remove('hidden');
        }

        function closeIncidentModal() {
            const modal = document.getElementById('incidentModal');
            if (modal) modal.classList.add('hidden');
        }

        // ==============================
        // MANUAL PLAY BUTTON FUNCTIONS
        // ==============================

        function showManualPlayButton() {
            document.getElementById('manualPlayBtn').classList.remove('hidden');
        }

        function hideManualPlayButton() {
            document.getElementById('manualPlayBtn').classList.add('hidden');
        }

        function enableSirenAudio() {
            userInteracted = true;
            if (isSirenActive) playSirenSound();
            hideManualPlayButton();
            showToast('Suara sirine telah diaktifkan');
        }

        // ==============================
        // USER INTERACTION HANDLING
        // ==============================

        function setupUserInteraction() {
            document.addEventListener('click', () => {
                if (!userInteracted) {
                    userInteracted = true;
                    if (isSirenActive && sirenAudio && sirenAudio.paused) setTimeout(() => playSirenSound(), 300);
                }
            });
            document.addEventListener('keydown', () => {
                userInteracted = true;
            });
        }

        // ==============================
        // NOTIFICATION FUNCTIONS
        // ==============================

        function showSirenAlert() {
            if (!sirenNotificationShown) {
                document.getElementById('sirenAlertBanner').classList.remove('hidden');
                sirenNotificationShown = true;
                incrementNotificationCount();
                updateSirenStatusUI(true);
                showToastSilent('⚠️ PEMBERITAHUAN DARURAT: Sirine telah diaktifkan!');
            }
        }

        function hideSirenAlert() {
            document.getElementById('sirenAlertBanner').classList.add('hidden');
            sirenNotificationShown = false;
            updateSirenStatusUI(false);
        }

        function dismissSirenAlert() {
            document.getElementById('sirenAlertBanner').classList.add('hidden');
        }

        function showToast(message) {
            showToastWithSound(message, true);
        }

        function showToastSilent(message) {
            showToastWithSound(message, false);
        }

        function showToastWithSound(message, playSound = true) {
            const toast = document.getElementById('toastNotification');
            const toastMessage = document.getElementById('toastMessage');
            if (!toast || !toastMessage) return;
            toastMessage.textContent = message;
            toast.classList.remove('hidden');
            if (playSound && userInteracted) playNotificationSound();
            setTimeout(() => toast.classList.add('hidden'), 5000);
        }

        function incrementNotificationCount() {
            notificationCount++;
            const counter = document.getElementById('notificationCount');
            if (counter) {
                counter.textContent = notificationCount;
                counter.classList.remove('hidden');
            }
        }

        function resetNotificationCount() {
            notificationCount = 0;
            const counter = document.getElementById('notificationCount');
            if (counter) {
                counter.textContent = '0';
                counter.classList.add('hidden');
            }
        }

        // ==============================
        // SIREN STATUS UI UPDATES
        // ==============================

        function updateSirenStatusUI(isActive) {
            isSirenActive = isActive;
            const sidebarStatus = document.getElementById('sidebarSirenStatus');
            if (sidebarStatus) isActive ? sidebarStatus.classList.remove('hidden') : sidebarStatus.classList.add('hidden');
            const headerStatus = document.getElementById('headerSirenStatus');
            if (headerStatus) isActive ? headerStatus.classList.remove('hidden') : headerStatus.classList.add('hidden');
            const miniBadge = document.getElementById('miniSirenBadge');
            if (miniBadge) isActive ? miniBadge.classList.remove('hidden') : miniBadge.classList.add('hidden');
            const statusIndicator = document.getElementById('sirenStatusIndicator');
            const statusText = document.getElementById('sirenStatusText');
            const statusCard = document.getElementById('sirenStatusCard');
            if (statusIndicator && statusText && statusCard) {
                if (isActive) {
                    statusIndicator.innerHTML = '<span class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></span><span class="text-xs font-medium text-red-600">AKTIF</span>';
                    statusText.textContent = 'Sirine sedang aktif - Keadaan Darurat';
                    statusCard.classList.add('border-red-300', 'bg-red-50');
                } else {
                    statusIndicator.innerHTML = '<span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span><span class="text-xs font-medium text-green-600">AMAN</span>';
                    statusText.textContent = 'Tidak ada sirine yang aktif';
                    statusCard.classList.remove('border-red-300', 'bg-red-50');
                }
            }
        }

        // ==============================
        // MQTT FUNCTIONS
        // ==============================

        function connectToMQTT() {
            try {
                client = mqtt.connect(broker, mqttOptions);
                client.on('connect', () => {
                    console.log('Admin Dashboard connected to MQTT');
                    updateMQTTStatus(true);
                    client.subscribe(topicAlarm, {
                        qos: 1
                    });
                    showToastSilent('Terhubung ke sistem MQTT');
                });
                client.on('message', (topic, payload) => {
                    const msg = payload.toString();
                    console.log(`MQTT Message [${topic}]: ${msg}`);
                    updateLastUpdateTime();
                    if (topic === topicAlarm) {
                        if (msg === 'ALARM_ON' && !isSirenActive) {
                            isSirenActive = true;
                            showSirenAlert();
                            setTimeout(() => playSirenSound(), 800);
                            showToastSilent('Sirine telah diaktifkan oleh pengguna');
                        }
                        if (msg === 'ALARM_OFF' && isSirenActive) {
                            isSirenActive = false;
                            hideSirenAlert();
                            stopSirenSound();
                            hideManualPlayButton();
                            showToastSilent('Sirine telah dimatikan');
                        }
                    }
                });
                client.on('error', (error) => {
                    console.error('MQTT Error:', error);
                    updateMQTTStatus(false);
                    showToastSilent('Koneksi MQTT terputus');
                });
                client.on('close', () => {
                    console.log('MQTT connection closed');
                    updateMQTTStatus(false);
                });
                client.on('reconnect', () => {
                    console.log('Reconnecting to MQTT...');
                    updateMQTTStatus(false);
                });
            } catch (error) {
                console.error('Failed to connect to MQTT:', error);
                updateMQTTStatus(false);
            }
        }

        function updateMQTTStatus(isConnected) {
            const statusElement = document.getElementById('mqttStatus');
            if (statusElement) {
                if (isConnected) {
                    statusElement.innerHTML = '<span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span><span class="text-xs font-medium text-green-600">Connected</span>';
                } else {
                    statusElement.innerHTML = '<span class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></span><span class="text-xs font-medium text-red-600">Disconnected</span>';
                }
            }
        }

        function updateLastUpdateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            const dateElement = document.getElementById('lastUpdateTime');
            if (dateElement) dateElement.textContent = timeString;
        }

        function initNotificationButton() {
            const notificationBtn = document.getElementById('notificationBtn');
            if (notificationBtn) notificationBtn.addEventListener('click', () => resetNotificationCount());
        }

        // ==============================
        // INITIALIZATION
        // ==============================

        document.addEventListener('DOMContentLoaded', () => {
            console.log('Admin Dashboard Initializing...');
            initializeAudioSystem();
            setupUserInteraction();
            connectToMQTT();
            initNotificationButton();
            updateLastUpdateTime();
            setTimeout(() => showToastSilent('Selamat datang di Dashboard Admin'), 1000);
        });
    </script>
</body>

</html>