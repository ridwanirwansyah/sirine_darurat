<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">

    <title>Master Control - Sisirin'e</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --app-height: 100dvh;
        }

        body {
            background: #f8fafc;
            margin: 0;
            height: var(--app-height);
            overflow: hidden;
        }

        .app-shell {
            max-width: 480px;
            margin: 0 auto;
            background: white;
            height: var(--app-height);
            display: flex;
            flex-direction: column;
            position: relative;
            padding-top: env(safe-area-inset-top);
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            padding-bottom: 90px;
            -webkit-overflow-scrolling: touch;
        }

        .main-button {
            transition: 0.3s;
            cursor: pointer;
        }

        .main-button:active {
            transform: scale(0.95);
        }

        .btn-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: auto;
            margin-bottom: calc(32px + env(safe-area-inset-bottom));
        }

        #masterBtn {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .nav-wrapper {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            padding-bottom: calc(15px + env(safe-area-inset-bottom));
            background: linear-gradient(to top, white 95%, transparent);
            z-index: 40;
        }

        .nav-bar {
            background: #0f172a;
            border-radius: 100px;
            height: 68px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 0 8px;
        }

        .modal-backdrop {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(8px);
        }

        .alert-banner {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }
            70% {
                box-shadow: 0 0 0 18px rgba(34, 197, 94, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
        }

        @keyframes tempFlash {
            0% {
                color: #0ea5e9;
                transform: scale(1.15);
            }
            100% {
                color: #334155;
                transform: scale(1);
            }
        }

        .temp-updated {
            animation: tempFlash 0.5s ease-out forwards;
        }

        .content-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #edf2f7;
            overflow: hidden;
        }

        .icon-small {
            width: 36px;
            height: 36px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pin-input {
            font-family: monospace;
            letter-spacing: 4px;
            font-size: 24px;
            text-align: center;
            font-weight: bold;
        }

        .pin-error {
            animation: shake 0.3s ease-in-out;
        }

        #pinHintValue {
            text-shadow: 0 0 10px rgba(124, 58, 237, 0.2);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        button {
            touch-action: manipulation;
        }

        .history-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: space-between;
        }

        .history-item {
            flex: 1 1 calc(50% - 4px);
            min-width: 130px;
            border-radius: 16px;
            padding: 12px 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .history-item-aktif {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .history-item-mati {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
        }

        .history-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .history-icon-aktif {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .history-icon-mati {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .history-content {
            flex: 1;
            min-width: 0;
        }

        .history-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .history-label-aktif { color: #166534; }
        .history-label-mati { color: #991b1b; }

        .history-time {
            font-size: 16px;
            font-weight: 800;
            line-height: 1.2;
            white-space: nowrap;
        }

        .history-time-aktif { color: #14532d; }
        .history-time-mati { color: #7f1d1d; }

        .history-date {
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 2px;
            white-space: nowrap;
        }

        .history-date-aktif { color: #4d7c0f; }
        .history-date-mati { color: #b91c1c; }

        @media (max-width: 380px) {
            .history-item { padding: 10px 8px; gap: 6px; }
            .history-icon { width: 36px; height: 36px; }
            .history-icon i { font-size: 14px; }
            .history-time { font-size: 14px; }
            .history-date { font-size: 8px; }
        }

        @media (max-width: 340px) {
            .history-container { flex-direction: column; }
            .history-item { width: 100%; }
        }

        .incident-badge {
            background-color: #fef9c3;
            border: 1px solid #fde047;
            color: #854d0e;
            font-size: 9px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 20px;
            white-space: nowrap;
        }

        .toast-notification {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #dc2626;
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            z-index: 200;
            animation: slideUpFade 0.3s ease-out;
            white-space: nowrap;
        }

        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translate(-50%, 20px);
            }
            to {
                opacity: 1;
                transform: translate(-50%, 0);
            }
        }

        .page-blocker {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(2px);
            z-index: 150;
            display: none;
        }

        .page-blocker.active { display: block; }

        #tempDot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #cbd5e1;
            display: inline-block;
            margin-right: 2px;
            transition: background 0.4s;
        }

        #tempDot.live { background: #22c55e; }

        #masterBtn {
            width: 208px !important;
            height: 208px !important;
            min-width: 208px !important;
            min-height: 208px !important;
            max-width: 208px !important;
            max-height: 208px !important;
            border-radius: 9999px;
            flex-shrink: 0;
        }

        #reportWhileActiveModal .modal-backdrop {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(8px);
        }

        #reportWhileActiveModal { z-index: 300; }
    </style>
</head>

<body>
    <div class="app-shell">
        <!-- HEADER -->
        <div class="px-4 pt-4 pb-2 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 shrink-0">
                <div class="w-9 h-9 bg-[#0f172a] rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-bell text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-black text-slate-800 leading-none">Sisirin'e</p>
                    <p class="text-[9px] text-slate-400 font-semibold leading-none mt-0.5">Sistem Sirine Sekolah</p>
                </div>
            </div>

            <div class="relative shrink-0" id="userMenuWrapper">
                <button onclick="toggleUserMenu()" class="bg-[#0f172a] rounded-full p-1.5 pl-4 flex items-center w-fit">
                    <div class="text-white mr-2 text-right">
                        <p class="text-[8px] opacity-70 font-bold uppercase">Welcome!</p>
                        <p class="text-xs font-bold truncate max-w-[100px]" id="userName">{{ $user->name ?? 'Ridwan' }}</p>
                    </div>
                    <div class="w-9 h-9 bg-white rounded-full flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user text-[#0f172a] text-sm"></i>
                    </div>
                </button>

                <div id="userDropdown" class="hidden absolute right-0 top-full mt-2 w-44 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50">
                    <button onclick="navigateTo('/user/profile')" class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-slate-50 transition">
                        <div class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0"><i class="fas fa-user text-blue-600 text-[10px]"></i></div>
                        <span class="text-xs font-bold text-slate-700">Profil</span>
                    </button>
                    <div class="h-px bg-slate-100 mx-3"></div>
                    <button onclick="handleLogout()" class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-red-50 transition">
                        <div class="w-7 h-7 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0"><i class="fas fa-sign-out-alt text-red-500 text-[10px]"></i></div>
                        <span class="text-xs font-bold text-red-500">Keluar</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="main-content px-4">
            <!-- INCIDENT ALERT -->
            <div id="incidentAlert" class="hidden mt-2 p-3 bg-red-50 border-2 border-red-200 rounded-xl">
                <div class="flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-red-600 text-sm flex-shrink-0"></i>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <p class="font-bold text-red-800 text-xs whitespace-nowrap">Ada Insiden Aktif!</p>
                            <span class="incident-badge flex-shrink-0"><i class="fas fa-clock mr-1"></i><span id="sirenStatusText">Belum Ditangani</span></span>
                            <button onclick="navigateTo('/user/incidents')" class="ml-auto flex-shrink-0 flex items-center gap-1 px-2 py-1 bg-red-600 text-white rounded-full text-[9px] font-bold hover:bg-red-700 active:scale-95 transition whitespace-nowrap"><i class="fas fa-eye text-[9px]"></i><span>Lihat</span></button>
                        </div>
                        <p id="incidentInfo" class="text-[10px] text-red-700 leading-tight mt-0.5 truncate"></p>
                    </div>
                </div>
            </div>

            <!-- HISTORY CARD -->
            <div class="mt-3 mb-4 content-card">
                <div class="bg-gradient-to-r from-slate-50 to-white px-3 py-2 border-b border-slate-100">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5"><i class="fas fa-history text-blue-500 text-[10px]"></i>Riwayat Aktivasi</h2>
                        <div class="flex items-center gap-1">
                            <i class="fas fa-calendar-alt text-slate-500 text-[10px]"></i>
                            <span class="text-[11px] font-bold text-slate-700 uppercase" id="currentDay">Sen</span>
                            <span class="text-[11px] font-bold text-slate-600" id="currentTime">00:00</span>
                            <div class="h-2.5 w-px bg-slate-300"></div>
                            <span id="tempDot"></span>
                            <i class="fas fa-thermometer-half text-slate-500 text-[10px]"></i>
                            <span class="text-[11px] font-bold text-slate-700" id="currentTemp">--°C</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <div id="statusDot" class="w-2 h-2 rounded-full bg-slate-300 flex-shrink-0" style="transition:background 0.4s"></div>
                        <span class="text-[10px] font-bold" id="statusLabel" style="transition:color 0.4s;color:#94a3b8">Memuat status...</span>
                        <span id="statusSince" class="text-[8px] text-slate-400 font-medium ml-0.5"></span>
                    </div>
                </div>

                <div class="p-3">
                    <div id="alarmHistoryLoading" class="hidden justify-center py-3"><div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div></div>
                    <div id="alarmHistoryContent" class="hidden">
                        <div class="history-container">
                            <div id="lastOnContainer" class="hidden history-item history-item-aktif">
                                <div class="history-icon history-icon-aktif"><i class="fa-solid fa-power-off text-sm"></i></div>
                                <div class="history-content">
                                    <div class="history-label history-label-aktif">AKTIF</div>
                                    <div class="history-time history-time-aktif" id="lastOnTime">--:--</div>
                                    <div class="history-date history-date-aktif" id="lastOnDate">-</div>
                                </div>
                            </div>
                            <div id="lastOffContainer" class="hidden history-item history-item-mati">
                                <div class="history-icon history-icon-mati"><i class="fa-solid fa-power-off text-sm"></i></div>
                                <div class="history-content">
                                    <div class="history-label history-label-mati">MATI</div>
                                    <div class="history-time history-time-mati" id="lastOffTime">--:--</div>
                                    <div class="history-date history-date-mati" id="lastOffDate">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-center"><p class="text-[7px] text-slate-400"><i class="fas fa-clock mr-0.5"></i><span id="historyTimestamp">Update: --:--</span></p></div>
                    </div>
                    <div id="noHistoryMessage" class="hidden text-center py-3"><div class="w-8 h-8 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-1"><i class="fas fa-clock text-slate-400 text-xs"></i></div><p class="text-[10px] text-slate-500">Belum ada riwayat</p></div>
                    <div id="alarmHistoryError" class="hidden text-center py-3"><div class="w-7 h-7 mx-auto bg-red-50 rounded-full flex items-center justify-center mb-1"><i class="fas fa-exclamation-triangle text-red-400 text-[9px]"></i></div><p class="text-[8px] text-red-500">Gagal memuat</p><button onclick="loadAlarmHistory()" class="mt-1 text-[7px] bg-blue-50 text-blue-600 font-bold px-2 py-0.5 rounded"><i class="fas fa-sync-alt mr-0.5"></i> Coba</button></div>
                </div>
            </div>

            <!-- MAIN BUTTON -->
            <div class="btn-wrapper">
                <button id="masterBtn" class="main-button bg-red-600 text-white border-[5px] border-white shadow-[0_10px_30px_rgba(220,38,38,0.30)] flex flex-col items-center justify-center flex-shrink-0">
                    <i id="btnIcon" class="fa-solid fa-triangle-exclamation text-4xl mb-2"></i>
                    <span id="labelMaster" class="text-5xl font-black tracking-tighter leading-none">MATI</span>
                    <p id="btnSubtitle" class="text-[8px] uppercase font-bold tracking-wide mt-2 text-center leading-tight opacity-90" style="max-width: 120px; word-break: break-word; white-space: normal;">Ketuk Sekali</p>
                </button>
            </div>
        </div>

        <!-- NAV BOTTOM -->
        <div class="nav-wrapper">
            <div class="nav-bar">
                <button onclick="navigateTo('/user/dashboard')" class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-red-500 text-2xl shadow-xl -mt-6 border-[6px] border-[#0f172a] shrink-0"><i class="fa-solid fa-house"></i></button>
                <button onclick="navigateTo('/user/incidents/create')" class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition shrink-0"><i class="fa-solid fa-exclamation-triangle"></i></button>
                <button onclick="navigateTo('/user/incidents')" class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition shrink-0"><i class="fa-solid fa-clipboard-list"></i></button>
                <button onclick="navigateTo('/user/riwayat')" class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl shrink-0"><i class="fa-solid fa-list-ul"></i></button>
                <button onclick="navigateTo('/user/profile')" class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl shrink-0"><i class="fa-solid fa-user"></i></button>
            </div>
        </div>
    </div>

    <!-- Page Blocker -->
    <div id="pageBlocker" class="page-blocker"></div>

    <!-- ALARM OFF MODAL -->
    <div id="alarmOffModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center modal-backdrop px-5">
        <div class="bg-white rounded-[35px] p-6 max-w-sm w-full text-center shadow-2xl">
            <div class="w-14 h-14 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fa-solid fa-power-off text-white text-xl"></i></div>
            <h3 class="text-gray-800 text-lg font-bold mb-2">Sirine Dimatikan</h3>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5"><div class="flex gap-3"><i class="fas fa-exclamation-triangle text-red-600 text-sm mt-0.5"></i><div class="text-left"><p class="text-sm text-red-800 font-semibold mb-1">Anda harus membuat laporan kejadian!</p><p class="text-xs text-red-700">Setelah sirine dimatikan, Anda wajib membuat laporan kejadian untuk mendokumentasikan insiden ini.</p></div></div></div>
            <div class="flex flex-col gap-2"><button onclick="createIncidentFromAlarm()" class="w-full py-4 bg-red-500 text-white rounded-xl font-black text-sm uppercase hover:bg-red-600 transition flex items-center justify-center gap-2 shadow-lg"><i class="fas fa-plus-circle"></i> BUAT LAPORAN SEKARANG</button></div>
        </div>
    </div>

    <!-- PIN MODAL -->
    <div id="pinModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center modal-backdrop px-5">
        <div class="bg-white rounded-[35px] p-6 max-w-sm w-full text-center shadow-2xl" data-user-pin="{{ $userPin ?? '' }}">
            <div class="w-14 h-14 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3"><i class="fa-solid fa-lock text-white text-xl"></i></div>
            <h3 class="text-gray-800 text-lg font-bold mb-1">Masukkan PIN</h3>
            <p class="text-gray-600 text-xs mb-3">Masukkan PIN 6 digit untuk mengaktifkan sirine</p>

            @if(!empty($userPin))
            <div class="mb-5 text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">PIN Anda</p>
                <div class="relative inline-block">
                    <p id="pinHintValue" class="text-2xl font-black text-slate-800 tracking-[6px]" style="font-family:monospace" data-pin="{{ $userPin }}" data-masked="{{ str_repeat('•', strlen($userPin)) }}">{{ str_repeat('•', strlen($userPin)) }}</p>
                    <div class="absolute -right-24 top-1/2 -translate-y-1/2 flex gap-2">
                        <button type="button" onclick="togglePinHint()" class="w-8 h-8 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center hover:scale-110 active:scale-95 transition shadow-sm"><i class="fas fa-eye text-xs" id="pinHintEyeIcon"></i></button>
                        <button type="button" onclick="salinPin()" class="w-8 h-8 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center hover:scale-110 active:scale-95 transition shadow-sm"><i class="fas fa-copy text-xs" id="copyPinIcon"></i></button>
                    </div>
                </div>
            </div>
            @endif

            <div class="relative mb-4">
                <input type="password" id="pinInput" maxlength="6" inputmode="numeric" pattern="\d*" autocomplete="off" class="pin-input w-full border-2 border-gray-200 rounded-xl px-4 py-3 pr-12 text-center text-xl font-mono focus:border-blue-500 focus:outline-none" placeholder="••••••">
                <button type="button" onclick="togglePinVisibility()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"><i class="fas fa-eye" id="pinEyeIcon"></i></button>
            </div>

            <div class="flex flex-col gap-2">
                <button id="verifyPinBtn" class="w-full py-3 bg-blue-600 text-white rounded-xl font-black text-xs uppercase hover:bg-blue-700 transition flex items-center justify-center gap-2"><i class="fas fa-check-circle"></i> Verifikasi & Aktifkan</button>
                <button id="cancelPinBtn" class="w-full py-3 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-200 transition">Batal</button>
            </div>
        </div>
    </div>

    <!-- PIN ERROR MODAL -->
    <div id="pinErrorModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center modal-backdrop px-5">
        <div class="bg-white rounded-[35px] p-6 max-w-sm w-full text-center shadow-2xl">
            <div class="w-14 h-14 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-3 animate-bounce"><i class="fa-solid fa-times-circle text-white text-xl"></i></div>
            <h3 class="text-gray-800 text-lg font-bold mb-1">PIN Salah!</h3>
            <p class="text-gray-600 text-xs mb-5">PIN yang Anda masukkan tidak sesuai. Silakan coba lagi.</p>
            <button onclick="retryPin()" class="w-full py-3 bg-red-600 text-white rounded-xl font-black text-xs uppercase hover:bg-red-700 transition">Coba Lagi</button>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div id="successModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center modal-backdrop px-5">
        <div class="bg-white rounded-[35px] p-6 max-w-sm w-full text-center shadow-2xl">
            <div class="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fa-solid fa-check text-white text-xl"></i></div>
            <h3 class="text-gray-800 text-lg font-bold mb-2">Sirine Aktif!</h3>
            <p class="text-gray-600 text-xs mb-5">Sirine telah diaktifkan.</p>
            <button onclick="document.getElementById('successModal').classList.add('hidden')" class="w-full py-3 bg-green-600 text-white rounded-xl font-black text-xs uppercase hover:bg-green-700 transition"><i class="fas fa-check mr-2"></i> Mengerti</button>
        </div>
    </div>

    <!-- REPORT WHILE ACTIVE MODAL -->
    <div id="reportWhileActiveModal" class="hidden fixed inset-0 z-[300] flex items-center justify-center modal-backdrop px-5">
        <div class="bg-white rounded-[35px] p-6 max-w-sm w-full text-center shadow-2xl">
            <div class="w-14 h-14 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse"><i class="fa-solid fa-bell text-white text-xl"></i></div>
            <h3 class="text-gray-800 text-lg font-bold mb-2">Sirine Sedang Aktif!</h3>
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-5"><div class="flex gap-3"><i class="fas fa-exclamation-triangle text-orange-600 text-sm mt-0.5"></i><div class="text-left"><p class="text-sm text-orange-800 font-semibold mb-1">Wajib Buat Laporan Kejadian</p><p class="text-xs text-orange-700">Sirine akan mati secara otomatis dalam waktu tertentu. Anda wajib membuat laporan kejadian untuk mendokumentasikan insiden ini. Laporan harus dibuat sebelum sirine mati.</p></div></div></div>
            <div class="flex flex-col gap-2"><button onclick="createReportWhileActive()" class="w-full py-4 bg-orange-500 text-white rounded-xl font-black text-sm uppercase hover:bg-orange-600 transition flex items-center justify-center gap-2 shadow-lg"><i class="fas fa-plus-circle"></i> BUAT LAPORAN SEKARANG</button></div>
        </div>
    </div>

    <!-- BLOCK ACCESS MODAL -->
    <div id="blockAccessModal" class="hidden fixed inset-0 z-[250] flex items-center justify-center modal-backdrop px-5">
        <div class="bg-white rounded-[35px] p-6 max-w-sm w-full text-center shadow-2xl">
            <div class="w-14 h-14 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fa-solid fa-ban text-white text-xl"></i></div>
            <h3 class="text-gray-800 text-lg font-bold mb-2">Akses Diblokir</h3>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5"><div class="flex gap-3"><i class="fas fa-exclamation-triangle text-red-600 text-sm mt-0.5"></i><div class="text-left"><p class="text-sm text-red-800 font-semibold mb-1">Anda harus membuat laporan terlebih dahulu!</p><p class="text-xs text-red-700">Setelah mematikan sirine, Anda wajib membuat laporan kejadian sebelum dapat mengakses halaman lain.</p></div></div></div>
            <div class="flex flex-col gap-2"><button onclick="createIncidentFromAlarm()" class="w-full py-4 bg-red-500 text-white rounded-xl font-black text-sm uppercase hover:bg-red-600 transition flex items-center justify-center gap-2 shadow-lg"><i class="fas fa-plus-circle"></i> BUAT LAPORAN SEKARANG</button></div>
        </div>
    </div>

    <script>
        // ==============================================
        // STATE MACHINE - Server Side Auto-Off
        // ==============================================

        const masterBtn = document.getElementById('masterBtn');
        const labelMaster = document.getElementById('labelMaster');
        const btnIcon = document.getElementById('btnIcon');
        const btnSubtitle = document.getElementById('btnSubtitle');
        const successModal = document.getElementById('successModal');
        const alarmOffModal = document.getElementById('alarmOffModal');
        const blockAccessModal = document.getElementById('blockAccessModal');
        const pageBlocker = document.getElementById('pageBlocker');
        const pinModal = document.getElementById('pinModal');
        const pinInput = document.getElementById('pinInput');
        const verifyPinBtn = document.getElementById('verifyPinBtn');
        const cancelPinBtn = document.getElementById('cancelPinBtn');
        const pinErrorModal = document.getElementById('pinErrorModal');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        let appState = 'OFF';
        let mqttConnected = false;
        let stateChangedAt = null;
        let currentAutoOffDuration = 60;
        let reportCheckInterval = null;
        let currentAlarmSessionId = null;
        let isReportCreatedForCurrentSession = false;
        let modalShownForCurrentSession = false;
        let pollingInterval = null;
        let directCheckInterval = null;

        function clearAllFlags() {
            localStorage.removeItem('from_active_alarm');
            localStorage.removeItem('need_report_redirect');
            localStorage.removeItem('alarm_activated_at');
            localStorage.removeItem('has_created_report_for_session');
            localStorage.removeItem('report_created_for_session');
            sessionStorage.removeItem('awaiting_report');
            isReportCreatedForCurrentSession = false;
            modalShownForCurrentSession = false;
        }

        // ==============================================
        // MQTT Configuration
        // ==============================================
        const mqttOptions = {
            username: 'lampu_control',
            password: 'Lampu_control123!',
            clientId: 'WebMaster_' + Math.random().toString(16).substr(2, 8),
            clean: true,
            reconnectPeriod: 5000,
            connectTimeout: 30000,
            keepalive: 60
        };
        const broker = 'wss://4037529a78a04d66ad4be0089551ad91.s1.eu.hivemq.cloud:8884/mqtt';
        const topicKendali = 'projekiot/lampu/kendali';
        const topicSuhu = 'projekiot/lampu/suhu';
        const client = mqtt.connect(broker, mqttOptions);

        // ==============================================
        // API Functions
        // ==============================================
        async function loadAutoOffDuration() {
            try {
                const response = await fetch('/user/alarm/current-state');
                const data = await response.json();
                currentAutoOffDuration = data.auto_off_duration || 60;
                if (appState === 'ON') btnSubtitle.textContent = `Sirine Menyala (Otomatis mati ${currentAutoOffDuration} detik)`;
            } catch (error) {
                console.error('Failed to load auto-off duration:', error);
                currentAutoOffDuration = 60;
            }
        }

        async function turnOnAlarm() {
            try {
                const response = await fetch('/user/alarm/on', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (data.success && data.session_id) {
                    localStorage.setItem('current_alarm_session_id', data.session_id);
                    currentAlarmSessionId = data.session_id;
                }
                return data;
            } catch (error) { console.error('Turn on alarm error:', error); return null; }
        }

        async function turnOffAlarm(trigger = 'manual') {
            try {
                const response = await fetch('/user/alarm/off', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ trigger: trigger })
                });
                return await response.json();
            } catch (error) { console.error('Turn off alarm error:', error); return null; }
        }

        async function markReportCreated(sessionId) {
            try {
                const response = await fetch('/user/alarm/mark-report-created', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ session_id: sessionId })
                });
                return await response.json();
            } catch (error) { console.error('Mark report created error:', error); return null; }
        }

        async function checkHasActiveOrPendingIncident() {
            try {
                const res = await fetch('/user/api/incidents/active');
                const data = await res.json();
                let list = data.incidents || data.data || (Array.isArray(data) ? data : []);
                return list.some(inc => { const s = (inc.status || '').toUpperCase(); return s === 'ACTIVE' || s === 'PENDING'; });
            } catch (err) { console.error('checkHasActiveOrPendingIncident error:', err); return false; }
        }

        async function checkReportForCurrentAlarmSession() {
            const alarmSessionId = localStorage.getItem('current_alarm_session_id');
            if (!alarmSessionId) return false;
            const reportCreatedFlag = localStorage.getItem('report_created_for_session');
            if (reportCreatedFlag === alarmSessionId) return true;
            try {
                const response = await fetch('/user/api/incidents/active');
                const data = await response.json();
                let incidents = data.incidents || data.data || [];
                const hasReportForSession = incidents.some(incident => incident.alarm_session_id === alarmSessionId);
                if (hasReportForSession) {
                    localStorage.setItem('report_created_for_session', alarmSessionId);
                    isReportCreatedForCurrentSession = true;
                }
                return hasReportForSession;
            } catch (error) { console.error('Error checking report for session:', error); return false; }
        }

        async function checkActiveIncidents() {
            try {
                const res = await fetch('/user/api/incidents/active');
                const data = await res.json();
                let list = data.incidents || data.data || [];
                const actives = list.filter(inc => (inc.status || '').toUpperCase() === 'ACTIVE');
                const alert = document.getElementById('incidentAlert');
                if (actives.length > 0) {
                    const inc = actives[0];
                    const type = inc.type || inc.type_code || 'Insiden';
                    let waktuLaporan = inc.reported_at_formatted || inc.reported_at;
                    if (waktuLaporan && typeof waktuLaporan === 'string') {
                        waktuLaporan = waktuLaporan
                            .replace(' ago', '')
                            .replace('minutes', 'menit yang lalu')
                            .replace('minute', 'menit yang lalu')
                            .replace('hours', 'jam yang lalu')
                            .replace('hour', 'jam yang lalu')
                            .replace('days', 'hari yang lalu')
                            .replace('day', 'hari yang lalu')
                            .replace('seconds', 'detik yang lalu')
                            .replace('second', 'detik yang lalu');
                    }
                    document.getElementById('incidentInfo').innerHTML = `<strong>${type}</strong> · Dilaporkan ${waktuLaporan || 'beberapa waktu lalu'}`;
                    alert?.classList.remove('hidden');
                } else { alert?.classList.add('hidden'); }
            } catch (err) { console.error('checkActiveIncidents error:', err); document.getElementById('incidentAlert')?.classList.add('hidden'); }
        }

        // ==============================================
        // Polling State dari Server - OPTIMIZED
        // ==============================================

        function startPollingState() {
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(async () => {
                if (document.visibilityState === 'visible') {
                    await syncStateFromServer();
                }
            }, 1000);
        }

        function stopPollingState() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        // ==============================================
        // FUNGSI SYNC UTAMA
        // ==============================================
        async function syncStateFromServer() {
            try {
                const response = await fetch('/user/alarm/current-state?_=' + Date.now());
                if (!response.ok) throw new Error('HTTP error ' + response.status);
                
                const data = await response.json();
                
                if (data.auto_off_duration) {
                    currentAutoOffDuration = data.auto_off_duration;
                }
                
                const serverState = data.is_on ? 'ON' : 'OFF';
                
                if (data.session_id && data.session_id !== currentAlarmSessionId) {
                    currentAlarmSessionId = data.session_id;
                    if (data.session_id) localStorage.setItem('current_alarm_session_id', data.session_id);
                }
                
                // PRIORITAS: Jika serverState OFF dan appState ON, LANGSUNG update UI
                if (serverState === 'OFF' && appState === 'ON') {
                    console.log('⚠️ AUTO-OFF DETECTED! Immediate UI update...');
                    appState = 'OFF';
                    stateChangedAt = Date.now();
                    sessionStorage.removeItem('alarm_state');
                    sessionStorage.removeItem('alarm_state_at');
                    clearAllFlags();
                    localStorage.removeItem('current_alarm_session_id');
                    currentAlarmSessionId = null;
                    isReportCreatedForCurrentSession = false;
                    modalShownForCurrentSession = false;
                    renderUI();
                    loadAlarmHistory();
                    checkActiveIncidents();
                    showToast('Sirine mati secara otomatis');
                    mqttPublish('ALARM_OFF');
                    return true;
                }
                
                if (serverState === 'ON' && appState !== 'ON') {
                    console.log('Server state: ON - Updating UI to ON');
                    appState = 'ON';
                    stateChangedAt = Date.now();
                    sessionStorage.setItem('alarm_state', 'ON');
                    sessionStorage.setItem('alarm_state_at', String(stateChangedAt));
                    renderUI();
                    const sessionId = localStorage.getItem('current_alarm_session_id');
                    if (sessionId) {
                        const needReport = localStorage.getItem('need_report_redirect');
                        const reportCreatedFlag = localStorage.getItem('report_created_for_session');
                        const reportCreated = (reportCreatedFlag === sessionId);
                        if (needReport === 'true' && !reportCreated && !isReportCreatedForCurrentSession) {
                            const hasReport = await checkReportForCurrentAlarmSession();
                            if (!hasReport && !modalShownForCurrentSession) {
                                setTimeout(() => showReportWhileActiveModal(), 500);
                            } else if (hasReport) {
                                localStorage.removeItem('need_report_redirect');
                                localStorage.setItem('report_created_for_session', sessionId);
                                isReportCreatedForCurrentSession = true;
                            }
                        }
                    }
                    loadAlarmHistory();
                    checkActiveIncidents();
                    return true;
                }
                
                if (appState === 'ON') {
                    if (data.remaining_seconds !== undefined && data.remaining_seconds > 0) {
                        btnSubtitle.textContent = `Sirine Menyala (Mati otomatis dalam ${data.remaining_seconds} detik)`;
                    } else {
                        btnSubtitle.textContent = `Sirine Menyala (Otomatis mati ${currentAutoOffDuration} detik)`;
                    }
                }
                
                return true;
            } catch (error) {
                console.error('Gagal sync state:', error);
                return false;
            }
        }

        // ==============================================
        // DIRECT AUTO-OFF CHECK (LEBIH CEPAT)
        // ==============================================
        async function checkDirectAutoOff() {
            try {
                const response = await fetch('/user/alarm/current-state?_=' + Date.now());
                const data = await response.json();
                if (!data.is_on && appState === 'ON') {
                    console.log('🚨 Direct auto-off detected! Immediate state change...');
                    appState = 'OFF';
                    stateChangedAt = Date.now();
                    sessionStorage.removeItem('alarm_state');
                    sessionStorage.removeItem('alarm_state_at');
                    clearAllFlags();
                    localStorage.removeItem('current_alarm_session_id');
                    currentAlarmSessionId = null;
                    isReportCreatedForCurrentSession = false;
                    modalShownForCurrentSession = false;
                    renderUI();
                    loadAlarmHistory();
                    checkActiveIncidents();
                    mqttPublish('ALARM_OFF');
                    showToast('Sirine mati secara otomatis');
                }
            } catch (error) {
                console.error('Direct check failed:', error);
            }
        }

        function startDirectAutoOffCheck() {
            if (directCheckInterval) clearInterval(directCheckInterval);
            directCheckInterval = setInterval(() => {
                if (document.visibilityState === 'visible') {
                    checkDirectAutoOff();
                }
            }, 1000);
        }

        function stopDirectAutoOffCheck() {
            if (directCheckInterval) {
                clearInterval(directCheckInterval);
                directCheckInterval = null;
            }
        }

        // ==============================================
        // FORCE SYNC
        // ==============================================
        async function forceSyncFromServer() {
            console.log('Force syncing state from server...');
            try {
                const response = await fetch('/user/alarm/current-state?_=' + Date.now());
                const data = await response.json();
                const serverState = data.is_on ? 'ON' : 'OFF';
                if (serverState !== appState) {
                    if (serverState === 'OFF') {
                        console.log('⚠️ Force sync: Turning OFF');
                        appState = 'OFF';
                        stateChangedAt = Date.now();
                        sessionStorage.removeItem('alarm_state');
                        sessionStorage.removeItem('alarm_state_at');
                        clearAllFlags();
                        localStorage.removeItem('current_alarm_session_id');
                        currentAlarmSessionId = null;
                        isReportCreatedForCurrentSession = false;
                        modalShownForCurrentSession = false;
                        renderUI();
                        loadAlarmHistory();
                        checkActiveIncidents();
                        mqttPublish('ALARM_OFF');
                    } else if (serverState === 'ON') {
                        console.log('Force sync: Turning ON');
                        appState = 'ON';
                        stateChangedAt = Date.now();
                        sessionStorage.setItem('alarm_state', 'ON');
                        sessionStorage.setItem('alarm_state_at', String(stateChangedAt));
                        renderUI();
                    }
                }
                return true;
            } catch (error) { console.error('Force sync failed:', error); return false; }
        }

        // ==============================================
        // SINKRONISASI ANTAR TAB
        // ==============================================
        window.addEventListener('storage', (event) => {
            if (event.key === 'current_alarm_session_id' || event.key === 'report_created_for_session') {
                console.log('Storage changed, syncing state...');
                forceSyncFromServer();
            }
        });

        // ==============================================
        // State Management
        // ==============================================
        function setState(newState) {
            if (newState === appState) return;
            console.log('Local state change:', appState, '→', newState);
            stateChangedAt = Date.now();

            if (newState === 'ON') {
                sessionStorage.setItem('alarm_state', 'ON');
                sessionStorage.setItem('alarm_state_at', String(stateChangedAt));
                startPollingState();
                startDirectAutoOffCheck();
            } else if (newState === 'OFF') {
                sessionStorage.removeItem('alarm_state');
                sessionStorage.removeItem('alarm_state_at');
                clearAllFlags();
                localStorage.removeItem('current_alarm_session_id');
                currentAlarmSessionId = null;
                isReportCreatedForCurrentSession = false;
                modalShownForCurrentSession = false;
                stopPollingState();
                stopDirectAutoOffCheck();
            } else if (newState === 'FORCED') {
                sessionStorage.removeItem('alarm_state');
                sessionStorage.removeItem('alarm_state_at');
            }

            appState = newState;
            renderUI();
        }

        function renderUI() {
            if (!masterBtn) return;
            const base = "main-button text-white border-[5px] border-white flex flex-col items-center justify-center flex-shrink-0";
            switch (appState) {
                case 'OFF':
                    masterBtn.className = base + " bg-red-600 shadow-[0_8px_20px_rgba(220,38,38,0.25)]";
                    btnIcon.className = "fa-solid fa-triangle-exclamation text-4xl mb-2";
                    labelMaster.textContent = "MATI";
                    btnSubtitle.textContent = "Ketuk Sekali";
                    pageBlocker.classList.remove('active');
                    break;
                case 'READY':
                    masterBtn.className = base + " bg-yellow-500 shadow-[0_8px_20px_rgba(245,158,11,0.25)]";
                    btnIcon.className = "fa-solid fa-hand-pointer text-4xl mb-2";
                    labelMaster.textContent = "SIAP";
                    btnSubtitle.textContent = "Masukkan PIN";
                    pageBlocker.classList.remove('active');
                    break;
                case 'ON':
                    masterBtn.className = base + " bg-green-600 shadow-[0_8px_20px_rgba(34,197,94,0.25)] pulse-animation";
                    btnIcon.className = "fa-solid fa-bullhorn text-4xl mb-2";
                    labelMaster.textContent = "NYALA";
                    btnSubtitle.textContent = `Sirine Menyala (Otomatis mati ${currentAutoOffDuration} detik)`;
                    pageBlocker.classList.remove('active');
                    break;
                case 'FORCED':
                    masterBtn.className = base + " bg-red-600 shadow-[0_8px_20px_rgba(220,38,38,0.25)]";
                    btnIcon.className = "fa-solid fa-triangle-exclamation text-4xl mb-2";
                    labelMaster.textContent = "MATI";
                    btnSubtitle.textContent = "Buat Laporan Dulu";
                    pageBlocker.classList.add('active');
                    break;
            }
            updateStatusBadge();
        }

        function updateStatusBadge() {
            const dot = document.getElementById('statusDot');
            const label = document.getElementById('statusLabel');
            const since = document.getElementById('statusSince');
            if (!dot || !label) return;
            if (appState === 'ON') {
                dot.style.background = '#16a34a';
                dot.style.boxShadow = '0 0 0 3px rgba(34,197,94,0.25)';
                label.textContent = 'Sirine Aktif';
                label.style.color = '#15803d';
            } else if (appState === 'READY') {
                dot.style.background = '#d97706';
                dot.style.boxShadow = '0 0 0 3px rgba(245,158,11,0.2)';
                label.textContent = 'Siap Diaktifkan';
                label.style.color = '#b45309';
            } else {
                dot.style.background = '#ef4444';
                dot.style.boxShadow = 'none';
                label.textContent = 'Sirine Tidak Aktif';
                label.style.color = '#94a3b8';
            }
            if (since && stateChangedAt) {
                const diffSec = Math.floor((Date.now() - stateChangedAt) / 1000);
                if (diffSec < 60) since.textContent = `· baru saja`;
                else if (diffSec < 3600) since.textContent = `· ${Math.floor(diffSec / 60)} menit lalu`;
                else since.textContent = `· ${Math.floor(diffSec / 3600)} jam lalu`;
            } else if (since) { since.textContent = ''; }
        }

        async function doAlarmOn() {
            console.log('Activating alarm via server...');
            await loadAutoOffDuration();
            clearAllFlags();
            modalShownForCurrentSession = false;
            localStorage.setItem('alarm_activated_at', new Date().toISOString());
            localStorage.setItem('need_report_redirect', 'true');
            verifyPinBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
            verifyPinBtn.disabled = true;
            const result = await turnOnAlarm();
            if (result && result.success) {
                mqttPublish('ALARM_ON');
                setState('ON');
                pinModal.classList.add('hidden');
                setTimeout(() => successModal.classList.remove('hidden'), 300);
                setTimeout(() => {
                    successModal.classList.add('hidden');
                    if (!modalShownForCurrentSession) {
                        showReportWhileActiveModal();
                        modalShownForCurrentSession = true;
                    }
                }, 2000);
                setTimeout(() => { loadAlarmHistory(); checkActiveIncidents(); }, 600);
            } else {
                showToast(result?.message || 'Gagal menyalakan sirine');
                setState('OFF');
            }
            verifyPinBtn.innerHTML = '<i class="fas fa-check-circle"></i> Verifikasi & Aktifkan';
            verifyPinBtn.disabled = false;
        }

        async function doAlarmOff() {
            console.log('Deactivating alarm via server...');
            const result = await turnOffAlarm('manual');
            if (result && result.success) {
                mqttPublish('ALARM_OFF');
                setState('OFF');
                setTimeout(() => { loadAlarmHistory(); checkActiveIncidents(); }, 600);
                const hasAnyReport = await checkHasActiveOrPendingIncident();
                if (!hasAnyReport) enterForcedState();
            } else { console.error('Failed to turn off alarm:', result?.message); }
        }

        function enterForcedState() { setState('FORCED'); if (alarmOffModal) alarmOffModal.classList.remove('hidden'); }
        function mqttPublish(msg) { if (client && mqttConnected) { try { client.publish(topicKendali, msg, { qos: 1 }); console.log('MQTT published:', msg); } catch (e) { console.error('MQTT publish error:', e); } } else { console.warn('MQTT not connected, cannot publish:', msg); } }

        // ==============================================
        // Modal Functions
        // ==============================================
        function showReportWhileActiveModal() {
            if (modalShownForCurrentSession) return;
            let modal = document.getElementById('reportWhileActiveModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'reportWhileActiveModal';
                modal.className = 'hidden fixed inset-0 z-[300] flex items-center justify-center modal-backdrop px-5';
                modal.innerHTML = `<div class="bg-white rounded-[35px] p-6 max-w-sm w-full text-center shadow-2xl"><div class="w-14 h-14 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse"><i class="fa-solid fa-bell text-white text-xl"></i></div><h3 class="text-gray-800 text-lg font-bold mb-2">Wajib Buat Laporan Kejadian!</h3><div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-5"><div class="flex gap-3"><i class="fas fa-exclamation-triangle text-orange-600 text-sm mt-0.5"></i><div class="text-left"><p class="text-sm text-orange-800 font-semibold mb-1">Setiap Aktivasi Sirine Wajib Dilaporkan</p><p class="text-xs text-orange-700">Anda harus membuat laporan kejadian untuk setiap kali sirine dinyalakan. Laporan ini akan menjadi dokumentasi resmi kejadian.</p></div></div></div><div class="flex flex-col gap-2"><button onclick="createReportWhileActive()" class="w-full py-4 bg-orange-500 text-white rounded-xl font-black text-sm uppercase hover:bg-orange-600 transition flex items-center justify-center gap-2 shadow-lg"><i class="fas fa-plus-circle"></i> BUAT LAPORAN SEKARANG</button></div></div>`;
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
            modalShownForCurrentSession = true;
        }

        function closeReportWhileActiveModal() { const modal = document.getElementById('reportWhileActiveModal'); if (modal) modal.classList.add('hidden'); }
        async function createReportWhileActive() { closeReportWhileActiveModal(); const alarmSessionId = localStorage.getItem('current_alarm_session_id'); const alarmActivatedAt = localStorage.getItem('alarm_activated_at'); localStorage.setItem('from_active_alarm', 'true'); location.href = `/user/incidents/create?source=active_alarm&session_id=${alarmSessionId}&activated_at=${alarmActivatedAt}`; }

        async function handleReturnFromReport() {
            const fromActiveAlarm = localStorage.getItem('from_active_alarm');
            const alarmSessionId = localStorage.getItem('current_alarm_session_id');
            if (fromActiveAlarm === 'true') {
                localStorage.removeItem('from_active_alarm');
                await new Promise(resolve => setTimeout(resolve, 1000));
                const hasReportForSession = await checkReportForCurrentAlarmSession();
                if (hasReportForSession) {
                    localStorage.removeItem('need_report_redirect');
                    localStorage.setItem('report_created_for_session', alarmSessionId);
                    isReportCreatedForCurrentSession = true;
                    await markReportCreated(alarmSessionId);
                    showToast('Laporan berhasil dibuat. Terima kasih!');
                    if (reportCheckInterval) { clearInterval(reportCheckInterval); reportCheckInterval = null; }
                    setTimeout(() => { window.location.href = '/user/dashboard'; }, 1500);
                } else {
                    showToast('Anda WAJIB membuat laporan kejadian untuk aktivasi sirine ini');
                    if (!window.location.pathname.includes('/incidents/create')) window.location.href = `/user/incidents/create?source=active_alarm&session_id=${alarmSessionId}`;
                }
            }
        }

        function startReportMonitoring() {
            if (reportCheckInterval) clearInterval(reportCheckInterval);
            reportCheckInterval = setInterval(async () => {
                if (appState === 'ON') {
                    const alarmSessionId = localStorage.getItem('current_alarm_session_id');
                    const reportCreatedFlag = localStorage.getItem('report_created_for_session');
                    if (reportCreatedFlag === alarmSessionId) { if (reportCheckInterval) { clearInterval(reportCheckInterval); reportCheckInterval = null; } return; }
                    const hasReportForSession = await checkReportForCurrentAlarmSession();
                    if (hasReportForSession) {
                        closeReportWhileActiveModal();
                        localStorage.removeItem('need_report_redirect');
                        localStorage.setItem('report_created_for_session', alarmSessionId);
                        isReportCreatedForCurrentSession = true;
                        if (reportCheckInterval) { clearInterval(reportCheckInterval); reportCheckInterval = null; }
                    }
                }
            }, 3000);
        }

        // ==============================================
        // History Functions
        // ==============================================
        async function loadAlarmHistory() {
            const loading = document.getElementById('alarmHistoryLoading');
            const content = document.getElementById('alarmHistoryContent');
            const empty = document.getElementById('noHistoryMessage');
            const error = document.getElementById('alarmHistoryError');
            if (!loading) return;
            loading.classList.remove('hidden'); loading.classList.add('flex');
            content.classList.add('hidden'); empty.classList.add('hidden'); error.classList.add('hidden');
            try {
                const response = await fetch('/user/alarm/history?_=' + Date.now());
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                loading.classList.add('hidden'); loading.classList.remove('flex');
                if (!data.success || !data.data?.length) { empty.classList.remove('hidden'); return; }
                const alarmOns = data.data.filter(i => i.action === 'ALARM_ON');
                const alarmOffs = data.data.filter(i => i.action === 'ALARM_OFF');
                const autoOffs = data.data.filter(i => i.action === 'AUTO_OFF');
                const onBox = document.getElementById('lastOnContainer');
                const offBox = document.getElementById('lastOffContainer');
                if (alarmOns.length && onBox) {
                    const lastOn = alarmOns[0];
                    const d = parseDate(lastOn.created_at);
                    document.getElementById('lastOnTime').textContent = fmtTime(d);
                    document.getElementById('lastOnDate').textContent = fmtDay(d);
                    onBox.classList.remove('hidden');
                } else if (onBox) { onBox.classList.add('hidden'); }
                let lastOff = null;
                if (alarmOffs.length > 0 && autoOffs.length > 0) {
                    const lastOffTime = new Date(alarmOffs[0].created_at);
                    const lastAutoOffTime = new Date(autoOffs[0].created_at);
                    lastOff = lastOffTime > lastAutoOffTime ? alarmOffs[0] : autoOffs[0];
                } else if (alarmOffs.length > 0) { lastOff = alarmOffs[0]; }
                else if (autoOffs.length > 0) { lastOff = autoOffs[0]; }
                if (lastOff && offBox) {
                    const d = parseDate(lastOff.created_at);
                    document.getElementById('lastOffTime').textContent = fmtTime(d);
                    document.getElementById('lastOffDate').textContent = fmtDay(d);
                    const offLabel = document.querySelector('#lastOffContainer .history-label');
                    if (offLabel) {
                        if (lastOff.action === 'AUTO_OFF') {
                            offLabel.textContent = 'AUTO OFF';
                            offLabel.classList.remove('history-label-mati');
                            offLabel.classList.add('history-label-aktif');
                        } else {
                            offLabel.textContent = 'MATI';
                            offLabel.classList.remove('history-label-aktif');
                            offLabel.classList.add('history-label-mati');
                        }
                    }
                    offBox.classList.remove('hidden');
                } else if (offBox) { offBox.classList.add('hidden'); }
                const ts = document.getElementById('historyTimestamp');
                if (ts) ts.textContent = 'Update: ' + fmtTime(new Date());
                content.classList.remove('hidden');
            } catch (error) {
                console.error('Error loading alarm history:', error);
                loading.classList.add('hidden'); loading.classList.remove('flex');
                error.classList.remove('hidden');
            }
        }

        // ==============================================
        // PIN Modal Functions
        // ==============================================
        function openPinModal() { if (pinInput) pinInput.value = ''; resetPinModal(); pinModal.classList.remove('hidden'); setTimeout(() => pinInput?.focus(), 150); }
        function resetPinModal() {
            pinShowVisible = false; pinHintVisible = false;
            const inp = document.getElementById('pinInput');
            const eyeIcon = document.getElementById('pinEyeIcon');
            const hintVal = document.getElementById('pinHintValue');
            const hintEye = document.getElementById('pinHintEyeIcon');
            if (inp) { inp.type = 'password'; inp.value = ''; }
            if (eyeIcon) eyeIcon.className = 'fas fa-eye';
            if (hintVal) hintVal.textContent = hintVal.dataset.masked || '';
            if (hintEye) hintEye.className = 'fas fa-eye';
        }
        async function handleVerifyPin() {
            const pin = pinInput?.value.trim() || '';
            if (!pin || pin.length !== 6 || !/^\d{6}$/.test(pin)) { showToast('PIN harus 6 digit angka'); return; }
            const origHTML = verifyPinBtn.innerHTML;
            verifyPinBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memverifikasi...';
            verifyPinBtn.disabled = true;
            try {
                const valid = await verifyPin(pin);
                if (valid) { await doAlarmOn(); }
                else { pinModal.classList.add('hidden'); pinErrorModal.classList.remove('hidden'); setState('READY'); }
            } catch (err) { console.error('handleVerifyPin error:', err); showToast('Gagal verifikasi PIN, coba lagi'); }
            finally { verifyPinBtn.innerHTML = origHTML; verifyPinBtn.disabled = false; if (pinInput) pinInput.value = ''; }
        }
        async function verifyPin(pin) { const res = await fetch('/user/verify-pin', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify({ pin }) }); const data = await res.json(); return data.valid === true; }
        function retryPin() { pinErrorModal.classList.add('hidden'); openPinModal(); }
        function salinPin() { const el = document.getElementById('pinHintValue'); const icon = document.getElementById('copyPinIcon'); if (!el) return; const pin = el.dataset.pin; if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(pin).then(() => { suksesCopy(icon); }).catch(() => { fallbackCopy(pin, icon); }); } else { fallbackCopy(pin, icon); } }
        function fallbackCopy(text, icon) { const textarea = document.createElement("textarea"); textarea.value = text; textarea.style.position = "fixed"; textarea.style.opacity = "0"; document.body.appendChild(textarea); textarea.focus(); textarea.select(); try { document.execCommand("copy"); suksesCopy(icon); } catch (err) { showToast('Gagal menyalin PIN'); } document.body.removeChild(textarea); }
        function suksesCopy(icon) { icon.classList.remove('fa-copy'); icon.classList.add('fa-check'); icon.classList.add('text-green-500'); showToast('PIN berhasil disalin'); setTimeout(() => { icon.classList.remove('fa-check', 'text-green-500'); icon.classList.add('fa-copy'); }, 2000); }

        // ==============================================
        // Navigation & Utility Functions
        // ==============================================
        function navigateTo(url) {
            const needReportRedirect = localStorage.getItem('need_report_redirect');
            const alarmSessionId = localStorage.getItem('current_alarm_session_id');
            const reportCreatedFlag = localStorage.getItem('report_created_for_session');
            const reportCreated = (reportCreatedFlag === alarmSessionId);
            if (appState === 'ON' && needReportRedirect === 'true' && !reportCreated && !isReportCreatedForCurrentSession) { showToast('Anda WAJIB membuat laporan kejadian terlebih dahulu'); window.location.href = `/user/incidents/create?source=required&session_id=${alarmSessionId}`; return false; }
            if (appState === 'FORCED') { blockAccessModal.classList.remove('hidden'); return false; }
            location.href = url; return true;
        }
        function createIncidentFromAlarm() { localStorage.setItem('from_alarm_activation', 'true'); localStorage.setItem('alarm_activated_at', new Date().toISOString()); sessionStorage.setItem('awaiting_report', 'true'); if (alarmOffModal) alarmOffModal.classList.add('hidden'); blockAccessModal.classList.add('hidden'); appState = 'OFF'; pageBlocker.classList.remove('active'); location.href = '/user/incidents/create?source=alarm_off'; }
        function showToast(msg) { const el = document.createElement('div'); el.className = 'toast-notification'; el.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i>${msg}`; document.body.appendChild(el); setTimeout(() => el.remove(), 3000); }
        function updateTemperature(value) { const tempEl = document.getElementById('currentTemp'); const dotEl = document.getElementById('tempDot'); if (!tempEl) return; const num = parseFloat(value); if (isNaN(num)) return; tempEl.textContent = num.toFixed(1) + '°C'; tempEl.classList.remove('temp-updated'); void tempEl.offsetWidth; tempEl.classList.add('temp-updated'); if (dotEl) { dotEl.classList.add('live'); clearTimeout(window._tempDotTimer); window._tempDotTimer = setTimeout(() => dotEl.classList.remove('live'), 30000); } }
        function updateDateTime() { const now = new Date(); const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']; const d = document.getElementById('currentDay'); const t = document.getElementById('currentTime'); if (d) d.textContent = days[now.getDay()]; if (t) t.textContent = fmtTime(now); }
        function parseDate(str) { if (!str) return new Date(NaN); return new Date(str.includes('T') ? str : str.replace(' ', 'T')); }
        function fmtTime(d) { if (!d || isNaN(d)) return '--:--'; return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }); }
        function fmtDay(d) { if (!d || isNaN(d)) return '-'; const today = new Date(); today.setHours(0,0,0,0); const yday = new Date(today); yday.setDate(today.getDate()-1); const cmp = new Date(d); cmp.setHours(0,0,0,0); if (+cmp === +today) return 'Hari ini'; if (+cmp === +yday) return 'Kemarin'; return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }); }
        function toggleUserMenu() { document.getElementById('userDropdown').classList.toggle('hidden'); }
        function handleLogout() { if (appState === 'FORCED') { showToast('Buat laporan dulu sebelum logout'); return; } const form = document.createElement('form'); form.method = 'POST'; form.action = '/logout'; const csrf = document.createElement('input'); csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = csrfToken; form.appendChild(csrf); document.body.appendChild(form); form.submit(); }
        let pinShowVisible = false; let pinHintVisible = false;
        function togglePinVisibility() { const inp = document.getElementById('pinInput'); const icon = document.getElementById('pinEyeIcon'); if (!inp) return; pinShowVisible = !pinShowVisible; inp.type = pinShowVisible ? 'text' : 'password'; icon.className = pinShowVisible ? 'fas fa-eye-slash' : 'fas fa-eye'; }
        function togglePinHint() { const valEl = document.getElementById('pinHintValue'); const icon = document.getElementById('pinHintEyeIcon'); if (!valEl) return; pinHintVisible = !pinHintVisible; valEl.textContent = pinHintVisible ? valEl.dataset.pin : valEl.dataset.masked; icon.className = pinHintVisible ? 'fas fa-eye-slash' : 'fas fa-eye'; valEl.classList.remove('temp-updated'); void valEl.offsetWidth; valEl.classList.add('temp-updated'); }

        if (masterBtn) {
            masterBtn.onclick = () => {
                const needReportRedirect = localStorage.getItem('need_report_redirect');
                const alarmSessionId = localStorage.getItem('current_alarm_session_id');
                const reportCreatedFlag = localStorage.getItem('report_created_for_session');
                const reportCreated = (reportCreatedFlag === alarmSessionId);
                if (appState === 'ON' && needReportRedirect === 'true' && !reportCreated && !isReportCreatedForCurrentSession) { showToast('Anda WAJIB membuat laporan kejadian terlebih dahulu'); window.location.href = `/user/incidents/create?source=required&session_id=${alarmSessionId}`; return; }
                switch (appState) {
                    case 'OFF': setState('READY'); break;
                    case 'READY': openPinModal(); break;
                    case 'ON': showReportWhileActiveModal(); break;
                    case 'FORCED': if (alarmOffModal) alarmOffModal.classList.remove('hidden'); break;
                }
            };
        }
        if (cancelPinBtn) cancelPinBtn.onclick = () => { pinModal.classList.add('hidden'); resetPinModal(); setState('OFF'); };
        if (verifyPinBtn) verifyPinBtn.onclick = () => handleVerifyPin();
        if (pinInput) pinInput.addEventListener('keypress', e => { if (e.key === 'Enter') { e.preventDefault(); handleVerifyPin(); } });
        document.addEventListener('input', e => { if (e.target.id === 'pinInput') e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6); });
        document.addEventListener('click', e => { const wrapper = document.getElementById('userMenuWrapper'); if (wrapper && !wrapper.contains(e.target)) document.getElementById('userDropdown')?.classList.add('hidden'); });

        // MQTT Events
        client.on('connect', () => {
            console.log('MQTT Connected ✓');
            mqttConnected = true;
            client.subscribe(topicKendali, { qos: 1 }, err => { if (err) console.error('Subscribe kendali GAGAL:', err); else console.log('Subscribe OK:', topicKendali); });
            client.subscribe(topicSuhu, { qos: 1 }, err => { if (err) console.error('Subscribe suhu GAGAL:', err); else console.log('Subscribe OK:', topicSuhu); });
        });
        client.on('message', (receivedTopic, payload) => {
            const msg = payload.toString().trim();
            if (receivedTopic === topicKendali || receivedTopic.endsWith('/kendali')) {
                if (msg === 'ALARM_ON' && appState !== 'ON') syncStateFromServer();
                if (msg === 'ALARM_OFF' && appState === 'ON') syncStateFromServer();
                loadAlarmHistory();
                setTimeout(checkActiveIncidents, 500);
            }
            if (receivedTopic === topicSuhu || receivedTopic.endsWith('/suhu')) updateTemperature(msg);
        });
        client.on('error', (err) => { console.error('MQTT Error:', err); mqttConnected = false; });
        client.on('close', () => { console.log('MQTT Disconnected'); mqttConnected = false; });
        client.on('offline', () => { console.log('MQTT Offline'); mqttConnected = false; });

        document.addEventListener('DOMContentLoaded', async () => {
            await loadAutoOffDuration();
            setInterval(updateDateTime, 1000);
            updateDateTime();
            await syncStateFromServer();
            const savedSessionId = localStorage.getItem('current_alarm_session_id');
            if (savedSessionId) currentAlarmSessionId = savedSessionId;
            const needReportRedirect = localStorage.getItem('need_report_redirect');
            const alarmSessionId = localStorage.getItem('current_alarm_session_id');
            const reportCreatedFlag = localStorage.getItem('report_created_for_session');
            const reportCreated = (reportCreatedFlag === alarmSessionId);
            if (appState === 'ON' && needReportRedirect === 'true' && !reportCreated) {
                const hasReportForSession = await checkReportForCurrentAlarmSession();
                if (!hasReportForSession) { showToast('Anda WAJIB membuat laporan kejadian untuk aktivasi sirine ini'); window.location.href = `/user/incidents/create?source=required&session_id=${alarmSessionId}`; return; }
                else { localStorage.removeItem('need_report_redirect'); localStorage.setItem('report_created_for_session', alarmSessionId); isReportCreatedForCurrentSession = true; }
            }
            await handleReturnFromReport();
            loadAlarmHistory();
            checkActiveIncidents();
            startReportMonitoring();
            setInterval(checkActiveIncidents, 15000);
            setInterval(loadAlarmHistory, 30000);
            setInterval(loadAutoOffDuration, 60000);
            successModal?.addEventListener('click', e => { if (e.target === successModal) successModal.classList.add('hidden'); });
            pinModal?.addEventListener('click', e => { if (e.target === pinModal) { pinModal.classList.add('hidden'); setState('OFF'); } });
            pinErrorModal?.addEventListener('click', e => { if (e.target === pinErrorModal) pinErrorModal.classList.add('hidden'); });
            if (alarmOffModal) alarmOffModal.addEventListener('click', e => { if (e.target === alarmOffModal) showToast('Anda harus membuat laporan terlebih dahulu'); });
            blockAccessModal?.addEventListener('click', e => { if (e.target === blockAccessModal) showToast('Anda harus membuat laporan terlebih dahulu'); });
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    console.log('Page became visible, forcing sync...');
                    setTimeout(() => forceSyncFromServer(), 100);
                }
            });
        });

        window.togglePinVisibility = togglePinVisibility;
        window.togglePinHint = togglePinHint;
        window.navigateTo = navigateTo;
        window.createIncidentFromAlarm = createIncidentFromAlarm;
        window.retryPin = retryPin;
        window.loadAlarmHistory = loadAlarmHistory;
        window.showToast = showToast;
        window.toggleUserMenu = toggleUserMenu;
        window.handleLogout = handleLogout;
        window.createReportWhileActive = createReportWhileActive;
        window.closeReportWhileActiveModal = closeReportWhileActiveModal;
        window.salinPin = salinPin;
        setInterval(updateStatusBadge, 30000);
    </script>
</body>

</html>