{{-- resources/views/admin/sirine.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sisirine™ - Manajemen Sirine</title>

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- MQTT Library --}}
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

    {{-- Global Styles --}}
    @include('admin.partials.styles')

    {{-- CSS Khusus Manajemen Sirine --}}
    <style>
        /* Pulse Animation */
        @keyframes pulse-ring {
            0% {
                transform: scale(0.95);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.7;
            }
            100% {
                transform: scale(0.95);
                opacity: 1;
            }
        }

        .pulse-animation {
            animation: pulse-ring 2s ease-in-out infinite;
        }

        /* Slider Styles */
        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: #ddd;
            outline: none;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #3b82f6;
            cursor: pointer;
        }

        input[type="range"]::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #3b82f6;
            cursor: pointer;
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
                'pageTitle' => 'Manajemen Sirine',
                'pageDescription' => 'Kontrol utama, status, dan pengaturan sirine sistem'
            ])

            {{-- MAIN CONTENT --}}
            <main class="main-scroll">

                {{-- KONTROL UTAMA SIRINE --}}
                <section class="mb-6 md:mb-8">
                    <div class="bg-white rounded-xl shadow-md p-4 md:p-6 border border-gray-200">
                        <h3 class="text-base md:text-lg font-bold text-gray-800 mb-4 md:mb-6 flex items-center">
                            <i class="fas fa-volume-up mr-2 md:mr-3 text-blue-600"></i>
                            Kontrol Utama Sirine
                        </h3>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">

                            {{-- CONTROL BUTTONS --}}
                            <div class="space-y-4 md:space-y-6">
                                {{-- Status Display --}}
                                <div class="bg-gray-50 rounded-lg p-4 border-2 border-gray-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-xs md:text-sm font-semibold text-gray-600">Status Sirine:</span>
                                        <span id="sirine-status-badge" class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                            <i class="fas fa-volume-mute mr-1"></i> MATI
                                        </span>
                                    </div>
                                    <div id="sirine-indicator" class="w-full h-2 bg-red-200 rounded-full"></div>
                                </div>

                                {{-- Control Buttons --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <button id="btn-on" onclick="publishOn()"
                                        class="bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-xl shadow-lg transition active:scale-95 flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-volume-up text-xl md:text-2xl"></i>
                                        <span class="text-xs md:text-sm">NYALAKAN</span>
                                    </button>

                                    <button id="btn-off" onclick="publishOff()"
                                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-4 rounded-xl shadow-lg transition active:scale-95 flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-volume-mute text-xl md:text-2xl"></i>
                                        <span class="text-xs md:text-sm">MATIKAN</span>
                                    </button>
                                </div>
                            </div>

                            {{-- STATUS INDICATORS --}}
                            <div class="space-y-3 md:space-y-4">
                                <div class="bg-white border-2 border-gray-200 rounded-lg p-3 md:p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2 md:space-x-3">
                                            <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-satellite-dish text-blue-600 text-sm md:text-base"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 font-semibold uppercase">MQTT Broker</p>
                                                <p class="text-xs md:text-sm font-bold text-gray-800">Status Koneksi</p>
                                            </div>
                                        </div>
                                        <span id="mqtt-status" class="px-2 md:px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                            <i class="fas fa-check-circle mr-1"></i> Terhubung
                                        </span>
                                    </div>
                                </div>

                                <div class="bg-white border-2 border-gray-200 rounded-lg p-3 md:p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2 md:space-x-3">
                                            <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-microchip text-purple-600 text-sm md:text-base"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 font-semibold uppercase">Perangkat ESP32</p>
                                                <p class="text-xs md:text-sm font-bold text-gray-800">Status Perangkat</p>
                                            </div>
                                        </div>
                                        <span id="device-status" class="px-2 md:px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                            <i class="fas fa-circle mr-1"></i> Online
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>

                {{-- DURASI AUTO-OFF SECTION --}}
                <section class="mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-stopwatch mr-3 text-blue-600"></i>
                            Pengaturan Mati Otomatis
                        </h3>

                        <div class="space-y-6">
                            {{-- Slider Display --}}
                            <div class="bg-blue-50 rounded-lg p-6 border-2 border-blue-200">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-sm font-semibold text-gray-700">Durasi Mati Otomatis:</span>
                                    <span class="text-3xl font-bold text-blue-600">
                                        <span id="duration-value">30</span> detik
                                    </span>
                                </div>
                                <input type="range" id="duration-slider" min="5" max="60" value="30" step="5" class="w-full">
                                <div class="flex justify-between text-xs text-gray-500 mt-2">
                                    <span>5 detik</span>
                                    <span>60 detik</span>
                                </div>
                            </div>

                            {{-- Save Button --}}
                            <button onclick="saveDuration()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition flex items-center justify-center space-x-2">
                                <i class="fas fa-save"></i>
                                <span>Simpan Pengaturan</span>
                            </button>

                            {{-- Info Text --}}
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-info-circle text-yellow-600 mt-1"></i>
                                    <div class="text-sm text-yellow-800">
                                        <p class="font-semibold mb-1">Informasi:</p>
                                        <p>Sirine akan mati secara otomatis setelah durasi yang ditentukan. Pengaturan ini akan tersimpan dan diterapkan pada sistem.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- LOG AKTIVITAS SIRINE SECTION --}}
                <section class="mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-history mr-3 text-blue-600"></i>
                            Log Aktivitas Sirine (5 Terakhir)
                        </h3>

                        {{-- Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b-2 border-gray-200">
                                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Pengguna</th>
                                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody id="logs-tbody" class="divide-y divide-gray-200">
                                    {{-- Logs will be loaded here --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- View All Button --}}
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <a href="/admin/riwayat" class="text-blue-600 hover:text-blue-700 font-semibold text-sm flex items-center justify-center py-2 transition">
                                Lihat Semua Log
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </section>

            </main>

        </div>
    </div>

    {{-- Global Scripts --}}
    @include('admin.partials.scripts')

    {{-- JS KHUSUS MANAJEMEN SIRINE --}}
    <script>
        /**
         * ========================================
         * MQTT CONNECTION & CONTROL FUNCTIONS
         * ========================================
         */

        let mqttClient = null;
        let sirineStatus = false;
        let autoOffAt = null;
        let autoOffTimer = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        /**
         * MQTT Configuration
         */
        const mqttOptions = {
            username: 'lampu_control',
            password: 'Lampu_control123!',
            clientId: 'WebAdmin_' + Math.random().toString(16).substr(2, 8),
            clean: true
        };

        const broker = 'wss://4037529a78a04d66ad4be0089551ad91.s1.eu.hivemq.cloud:8884/mqtt';
        const topic = 'projekiot/lampu/kendali';

        /**
         * Connect to MQTT Broker
         */
        function connectMQTT() {
            console.log('Connecting to MQTT Broker...');

            mqttClient = mqtt.connect(broker, mqttOptions);

            mqttClient.on('connect', () => {
                console.log('MQTT Connected');
                updateMQTTStatus(true);
                updateDeviceStatus(true);
                mqttClient.subscribe(topic);
            });

            mqttClient.on('message', (receivedTopic, payload) => {
                const msg = payload.toString();
                console.log('MQTT Message received:', receivedTopic, msg);

                if (msg === 'ALARM_ON') {
                    sirineStatus = true;
                    updateSirineUI();
                    updateLastActivity('Sirine DINYALAKAN (dari device)');
                } else if (msg === 'ALARM_OFF') {
                    sirineStatus = false;
                    updateSirineUI();
                    updateLastActivity('Sirine DIMATIKAN (dari device)');
                }
            });

            mqttClient.on('disconnect', () => {
                console.log('MQTT Disconnected');
                updateMQTTStatus(false);
            });

            mqttClient.on('error', (err) => {
                console.error('MQTT Error:', err);
                updateMQTTStatus(false);
            });
        }

        /**
         * Publish ON command to MQTT
         */
        function publishOn() {
            console.log('Publishing: ALARM ON');

            // Send MQTT command
            if (mqttClient && mqttClient.connected) {
                mqttClient.publish(topic, 'ALARM_ON', {
                    qos: 1
                });
            }

            // Send log to backend
            fetch('/admin/alarm/log', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        action: 'ALARM_ON'
                    })
                }).then(r => r.json())
                .then(res => {
                    if (res.auto_off_at) {
                        autoOffAt = res.auto_off_at;
                        console.log('Auto-off will be triggered at:', autoOffAt);
                    }
                })
                .catch(err => console.error('Log error:', err));

            // Update UI
            sirineStatus = true;
            updateSirineUI();
            updateLastActivity('Sirine DINYALAKAN');

            // Show notification
            showNotification('Sirine berhasil dinyalakan!', 'success');
        }

        /**
         * Publish OFF command to MQTT
         */
        function publishOff() {
            console.log('Publishing: ALARM OFF');

            // Send MQTT command
            if (mqttClient && mqttClient.connected) {
                mqttClient.publish(topic, 'ALARM_OFF', {
                    qos: 1
                });
            }

            // Send log to backend
            fetch('/admin/alarm/log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    action: 'ALARM_OFF'
                })
            }).catch(err => console.error('Log error:', err));

            // Update UI
            sirineStatus = false;
            autoOffAt = null;
            updateSirineUI();
            updateLastActivity('Sirine DIMATIKAN');

            // Show notification
            showNotification('Sirine berhasil dimatikan!', 'success');
        }

        /**
         * Load current duration settings from backend
         */
        function loadDuration() {
            fetch('/alarm/status')
                .then(r => r.json())
                .then(res => {
                    const duration = res.auto_off_duration || 60;
                    document.getElementById('duration-slider').value = duration;
                    document.getElementById('duration-value').textContent = duration;
                    console.log('Duration loaded:', duration);
                })
                .catch(err => console.error('Error loading duration:', err));
        }

        /**
         * Save duration settings
         */
        function saveDuration() {
            const duration = document.getElementById('duration-slider').value;

            fetch('/admin/alarm/duration', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        duration: parseInt(duration)
                    })
                })
                .then(r => r.json())
                .then(res => {
                    console.log('Duration saved:', res);
                    showNotification(`Durasi auto-off berhasil disimpan: ${duration} detik`, 'success');
                })
                .catch(err => {
                    console.error('Error saving duration:', err);
                    showNotification('Gagal menyimpan durasi', 'error');
                });
        }

        /**
         * Check and handle auto-off timer
         */
        function checkAutoOff() {
            if (!sirineStatus || !autoOffAt) {
                return;
            }

            const now = new Date().getTime();
            const autoOffTime = new Date(autoOffAt).getTime();

            if (now >= autoOffTime) {
                console.log('Auto-off triggered! Turning off sirine...');
                publishOff();
            }
        }

        /**
         * ========================================
         * UI UPDATE FUNCTIONS
         * ========================================
         */

        /**
         * Update Sirine Status UI
         */
        function updateSirineUI() {
            const badge = document.getElementById('sirine-status-badge');
            const indicator = document.getElementById('sirine-indicator');

            if (sirineStatus) {
                badge.className = 'px-4 py-2 rounded-full text-sm font-bold bg-green-100 text-green-700 pulse-animation';
                badge.innerHTML = '<i class="fas fa-volume-up mr-1"></i> NYALA';
                indicator.className = 'w-full h-3 bg-green-500 rounded-full pulse-animation';
            } else {
                badge.className = 'px-4 py-2 rounded-full text-sm font-bold bg-red-100 text-red-700';
                badge.innerHTML = '<i class="fas fa-volume-mute mr-1"></i> MATI';
                indicator.className = 'w-full h-3 bg-red-200 rounded-full';
            }
        }

        /**
         * Update MQTT Status
         */
        function updateMQTTStatus(connected) {
            const statusEl = document.getElementById('mqtt-status');

            if (connected) {
                statusEl.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700';
                statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Terhubung';
            } else {
                statusEl.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700';
                statusEl.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Terputus';
            }
        }

        /**
         * Update Device Status
         */
        function updateDeviceStatus(online) {
            const statusEl = document.getElementById('device-status');

            if (online) {
                statusEl.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700';
                statusEl.innerHTML = '<i class="fas fa-circle mr-1"></i> Online';
            } else {
                statusEl.className = 'px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700';
                statusEl.innerHTML = '<i class="fas fa-circle mr-1"></i> Offline';
            }
        }

        /**
         * Update Last Activity
         */
        function updateLastActivity(action) {
            const activityEl = document.getElementById('last-activity');
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID');

            if (activityEl) {
                activityEl.textContent = `${action} - ${timeStr}`;
            }
        }

        /**
         * Show Notification
         */
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-20 right-6 z-50 px-6 py-4 rounded-lg shadow-lg transition-all transform translate-x-0 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                'bg-blue-500'
            } text-white font-semibold`;

            notification.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        /**
         * Get user badge HTML
         */
        function getUserBadgeHTML(userName, userRole) {
            const isAuto = userRole === 'AUTO' || userName === 'System';

            if (isAuto) {
                return `<div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-700">
                        <i class="fas fa-robot text-xs"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-gray-800">Sistem</p>
                        <p class="text-xs text-gray-500">Mati Otomatis</p>
                    </div>
                </div>`;
            }

            return `<div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-700">
                    <i class="fas fa-user text-xs"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm text-gray-800">${userName}</p>
                    <p class="text-xs text-gray-500">Admin</p>
                </div>
            </div>`;
        }

        /**
         * Load logs from backend
         */
        function loadLogs() {
            fetch('/admin/alarm/logs')
                .then(r => r.json())
                .then(res => {
                    const tbody = document.getElementById('logs-tbody');
                    if (!tbody) return;

                    tbody.innerHTML = '';

                    if (res.logs && res.logs.length > 0) {
                        const filteredLogs = res.logs.filter(log =>
                            log.action === 'ALARM_ON' || log.action === 'AUTO_OFF'
                        );

                        if (filteredLogs.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="3" class="py-8 px-4 text-center text-gray-500">Belum ada aktivitas sirine</td></tr>`;
                            return;
                        }

                        filteredLogs.slice(0, 5).forEach(log => {
                            const row = document.createElement('tr');
                            row.className = 'hover:bg-gray-50 transition';
                            const displayTime = log.event_time ? formatDate(log.event_time) : formatDate(log.created_at);

                            let actionLabel = log.action === 'ALARM_ON' ? 'DINYALAKAN' : 'MATI OTOMATIS';
                            let badgeClass = log.action === 'ALARM_ON' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700';
                            let iconClass = log.action === 'ALARM_ON' ? 'fa-volume-up' : 'fa-clock';

                            row.innerHTML = `
                                <td class="py-4 px-4">${getUserBadgeHTML(log.user_name, log.user_role)}</td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold ${badgeClass}">
                                        <i class="fas ${iconClass} mr-1"></i>${actionLabel}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-sm text-gray-600">${displayTime}</td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = `<tr><td colspan="3" class="py-8 px-4 text-center text-gray-500">Belum ada aktivitas sirine</td></tr>`;
                    }
                })
                .catch(err => {
                    console.error('Error loading logs:', err);
                    const tbody = document.getElementById('logs-tbody');
                    if (tbody) {
                        tbody.innerHTML = `<tr><td colspan="3" class="py-4 px-4 text-center text-red-500">Gagal memuat log</td></tr>`;
                    }
                });
        }

        /**
         * Format date to readable format
         */
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }

        /**
         * ========================================
         * DURATION SLIDER HANDLER
         * ========================================
         */
        function initDurationSlider() {
            const slider = document.getElementById('duration-slider');
            const valueDisplay = document.getElementById('duration-value');

            if (slider && valueDisplay) {
                slider.addEventListener('input', function() {
                    valueDisplay.textContent = this.value;
                });
            }
        }

        /**
         * ========================================
         * INITIALIZE ON PAGE LOAD
         * ========================================
         */
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize duration slider
            initDurationSlider();

            // Load duration from backend
            loadDuration();

            // Load logs
            loadLogs();

            // Refresh logs every 5 seconds
            setInterval(loadLogs, 5000);

            // Get initial status from backend
            fetch('/alarm/status')
                .then(r => r.json())
                .then(res => {
                    sirineStatus = res.is_on;
                    autoOffAt = res.auto_off_at;
                    updateSirineUI();
                })
                .catch(err => console.error('Error fetching alarm status:', err));

            // Check auto-off every second
            setInterval(checkAutoOff, 1000);

            // Connect to MQTT
            connectMQTT();

            // Initial UI state
            updateSirineUI();

            console.log('Sirine Management Page Initialized');
        });

        /**
         * ========================================
         * KEYBOARD SHORTCUTS
         * ========================================
         */
        document.addEventListener('keydown', (e) => {
            // Ctrl + 1 = Turn ON
            if (e.ctrlKey && e.key === '1') {
                e.preventDefault();
                publishOn();
            }

            // Ctrl + 2 = Turn OFF
            if (e.ctrlKey && e.key === '2') {
                e.preventDefault();
                publishOff();
            }
        });
    </script>
</body>
</html>