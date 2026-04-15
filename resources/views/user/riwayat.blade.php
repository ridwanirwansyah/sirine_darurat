<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Riwayat Aktivitas - Sisirin'e</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root { --app-height: 100dvh; }

        body {
            background: #f8fafc;
            margin: 0;
            height: var(--app-height);
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .app-shell {
            max-width: 480px;
            margin: 0 auto;
            height: var(--app-height);
            display: flex;
            flex-direction: column;
            background: white;
            position: relative;
            overflow: hidden;
        }

        /* ── HEADER ── */
        .page-header {
            flex-shrink: 0;
            background: white;
            padding: 14px 16px 12px;
            z-index: 10;
        }

        /* ── BOTTOM SHEET WRAPPER ── */
        .sheet-wrapper {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        /* ── BOTTOM SHEET ── */
        .bottom-sheet {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            background: #f1f5f9;
            border-top-left-radius: 24px;
            border-top-right-radius: 24px;
            transition: top 0.35s cubic-bezier(0.32, 0.72, 0, 1);
            display: flex;
            flex-direction: column;
        }

        /* Handle bar area — draggable */
        .sheet-handle-area {
            flex-shrink: 0;
            padding: 10px 0 6px;
            cursor: grab;
            touch-action: none;
            user-select: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .sheet-handle-area:active { cursor: grabbing; }

        .handle-bar {
            width: 36px;
            height: 4px;
            background: #cbd5e1;
            border-radius: 100px;
        }

        .handle-hint {
            font-size: 9px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── SCROLLABLE LIST ── */
        .sheet-scroll {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: calc(90px + env(safe-area-inset-bottom));
        }

        .sheet-scroll::-webkit-scrollbar { display: none; }

        /* ── LIST ITEM ── */
        .log-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            background: white;
            border-radius: 14px;
            margin: 0 12px 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .log-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 15px;
        }

        .log-icon.on  { background: #f0fdf4; color: #16a34a; }
        .log-icon.off { background: #fef2f2; color: #dc2626; }
        .log-icon.auto { background: #eff6ff; color: #2563eb; }

        .log-info { flex: 1; min-width: 0; }

        .log-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
        }

        .log-time {
            font-size: 10px;
            font-weight: 600;
            color: #94a3b8;
            margin-top: 2px;
        }

        .log-badge {
            font-size: 9px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 100px;
            flex-shrink: 0;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .log-badge.on  { background: #dcfce7; color: #15803d; }
        .log-badge.off { background: #fee2e2; color: #dc2626; }
        .log-badge.auto { background: #dbeafe; color: #1e40af; }

        /* ── DIVIDER TANGGAL ── */
        .date-divider {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px 5px;
        }

        .date-divider-line { flex: 1; height: 1px; background: #e2e8f0; }

        .date-divider-label {
            font-size: 9px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
            text-align: center;
        }

        .empty-icon {
            width: 56px;
            height: 56px;
            background: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #cbd5e1;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        /* ── NAV ── */
        .nav-wrapper {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            padding-bottom: calc(14px + env(safe-area-inset-bottom));
            background: linear-gradient(to top, rgba(255,255,255,0.97) 80%, transparent);
            pointer-events: none;
            z-index: 100;
        }

        .nav-bar {
            background: #0f172a;
            border-radius: 100px;
            height: 65px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            pointer-events: auto;
        }

        @media (max-height: 600px) {
            .page-header { padding: 10px 16px 8px; }
            .log-item { padding: 8px 12px; }
            .log-icon { width: 34px; height: 34px; font-size: 13px; }
            .log-title { font-size: 12px; }
        }
    </style>
</head>
<body>

<div class="app-shell shadow-2xl">

    <!-- HEADER — sama seperti dashboard -->
    <div class="page-header">
        <div class="flex items-center justify-between gap-2">
            <!-- Badge total kiri -->
            <div class="flex items-center gap-1 bg-slate-100 px-3 py-1.5 rounded-full shrink-0">
                <i class="fas fa-list-ul text-slate-500 text-xs"></i>
                <span class="text-[9px] font-black text-slate-700">{{ $logs->count() }} aktivitas</span>
            </div>

            <!-- User pill kanan dengan dropdown -->
            <div class="relative shrink-0" id="userMenuWrapper">
                <button onclick="toggleUserMenu()"
                    class="bg-[#0f172a] rounded-full p-1.5 pl-4 flex items-center w-fit">
                    <div class="text-white mr-2 text-right">
                        <p class="text-[8px] opacity-70 font-bold uppercase">Welcome!</p>
                        <p class="text-xs font-bold truncate max-w-[100px]">{{ $user->name }}</p>
                    </div>
                    <div class="w-9 h-9 bg-white rounded-full flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user text-[#0f172a] text-sm"></i>
                    </div>
                </button>

                <!-- Dropdown -->
                <div id="userDropdown"
                    class="hidden absolute right-0 top-full mt-2 w-44 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50">
                    <button onclick="location.href='/user/profile'"
                        class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-slate-50 transition">
                        <div class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-blue-600 text-[10px]"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-700">Profil</span>
                    </button>
                    <div class="h-px bg-slate-100 mx-3"></div>
                    <button onclick="handleLogout()"
                        class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-red-50 transition">
                        <div class="w-7 h-7 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-sign-out-alt text-red-500 text-[10px]"></i>
                        </div>
                        <span class="text-xs font-bold text-red-500">Keluar</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-3 flex items-center gap-2">
            <h1 class="text-lg font-black text-slate-800 tracking-tight">Riwayat Aktivitas</h1>
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide mt-0.5">Sirine</span>
        </div>
    </div>

    <!-- SHEET WRAPPER -->
    <div class="sheet-wrapper" id="sheetWrapper">

        <!-- BOTTOM SHEET (draggable) -->
        <div class="bottom-sheet" id="bottomSheet">

            <!-- Handle bar — drag area -->
            <div class="sheet-handle-area" id="sheetHandle">
                <div class="handle-bar"></div>
                <span class="handle-hint" id="handleHint">Geser ke bawah</span>
            </div>

            <!-- Scrollable list -->
            <div class="sheet-scroll" id="sheetScroll">

                @forelse ($logs as $log)
                @php
                    // Hanya tampilkan ALARM_ON dan AUTO_OFF
                    $isOn    = $log->action === 'ALARM_ON';
                    $isAuto  = $log->action === 'AUTO_OFF';
                    
                    // Lewati jika bukan ALARM_ON atau AUTO_OFF
                    if (!$isOn && !$isAuto) continue;
                    
                    $tgl     = $log->created_at->format('d/m/Y');
                    $prevLog = $loop->first ? null : $logs[$loop->index - 1];
                    $prevTgl = $prevLog ? $prevLog->created_at->format('d/m/Y') : null;
                    $showDiv = $loop->first || $tgl !== $prevTgl;

                    $today     = now()->format('d/m/Y');
                    $yesterday = now()->subDay()->format('d/m/Y');
                    if ($tgl === $today)         $dayLabel = 'Hari Ini';
                    elseif ($tgl === $yesterday) $dayLabel = 'Kemarin';
                    else                         $dayLabel = Carbon\Carbon::parse($log->created_at)->locale('id')->translatedFormat('d M Y');

                    // diffForHumans dalam bahasa Indonesia
                    $diffId = Carbon\Carbon::parse($log->created_at)->locale('id')->diffForHumans();
                    // Hapus kata "yang lalu" jika perlu
                    $diffId = str_replace(['yang lalu', 'ago'], '', $diffId);
                @endphp

                @if($showDiv)
                <div class="date-divider">
                    <div class="date-divider-line"></div>
                    <span class="date-divider-label">{{ $dayLabel }}</span>
                    <div class="date-divider-line"></div>
                </div>
                @endif

                <div class="log-item">
                    <div class="log-icon {{ $isOn ? 'on' : 'auto' }}">
                        <i class="fa-solid {{ $isOn ? 'fa-bell' : 'fa-clock' }}"></i>
                    </div>
                    <div class="log-info">
                        <div class="log-title">{{ $isOn ? 'Sirine Dinyalakan' : 'Sirine Mati Otomatis' }}</div>
                        <div class="log-time">
                            <i class="far fa-clock mr-0.5"></i>
                            {{ Carbon\Carbon::parse($log->created_at)->format('H:i') }} WIB · {{ trim($diffId) }}
                        </div>
                    </div>
                    <span class="log-badge {{ $isOn ? 'on' : 'auto' }}">{{ $isOn ? 'NYALA' : 'AUTO OFF' }}</span>
                </div>

                @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fa-solid fa-bell-slash"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-500 mb-1">Belum Ada Aktivitas</p>
                    <p class="text-xs text-slate-400 max-w-[220px]">
                        Aktivitas penyalaan dan auto-off sirine akan muncul di sini.
                    </p>
                </div>
                @endforelse

            </div><!-- /sheet-scroll -->
        </div><!-- /bottom-sheet -->
    </div><!-- /sheet-wrapper -->

    <!-- NAV -->
    <div class="nav-wrapper">
        <div class="nav-bar">
            <button onclick="location.href='/user/dashboard'"
                class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition">
                <i class="fa-solid fa-house"></i>
            </button>
            <button onclick="location.href='/user/incidents/create'"
                class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition">
                <i class="fa-solid fa-exclamation-triangle"></i>
            </button>
            <button onclick="location.href='/user/incidents'"
                class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition">
                <i class="fa-solid fa-clipboard-list"></i>
            </button>
            <button onclick="location.href='/user/riwayat'"
                class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-red-500 text-2xl shadow-xl -mt-6 border-[6px] border-[#0f172a]">
                <i class="fa-solid fa-list-ul"></i>
            </button>
            <button onclick="location.href='/user/profile'"
                class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition">
                <i class="fa-solid fa-user"></i>
            </button>
        </div>
    </div>

</div>

<script>
/* ==============================================
   DRAGGABLE BOTTOM SHEET
============================================== */

const sheet      = document.getElementById('bottomSheet');
const handle     = document.getElementById('sheetHandle');
const scroll     = document.getElementById('sheetScroll');
const wrapper    = document.getElementById('sheetWrapper');
const hint       = document.getElementById('handleHint');

let wrapperH     = 0;
let sheetH       = 0;
let topExpanded  = 0;
let topCollapsed = 0;
let currentTop   = 0;
let isExpanded   = false;

function calcPositions() {
    wrapperH     = wrapper.offsetHeight;
    topExpanded  = 0;
    topCollapsed = Math.round(wrapperH * 0.52);
}

function applyTop(top, animated) {
    sheet.style.transition = animated
        ? 'top 0.35s cubic-bezier(0.32,0.72,0,1)'
        : 'none';
    sheet.style.top = top + 'px';
    currentTop = top;
    updateHint();
}

function updateHint() {
    hint.textContent = isExpanded ? 'Geser ke bawah' : 'Geser ke atas';
}

function snapSheet() {
    const mid = (topExpanded + topCollapsed) / 2;
    if (currentTop < mid) {
        isExpanded = true;
        applyTop(topExpanded, true);
    } else {
        isExpanded = false;
        applyTop(topCollapsed, true);
    }
    scroll.style.overflowY = isExpanded ? 'auto' : 'hidden';
}

function initSheet() {
    calcPositions();
    isExpanded = true;
    applyTop(topExpanded, false);
    scroll.style.overflowY = 'auto';
}

window.addEventListener('resize', () => {
    calcPositions();
    applyTop(isExpanded ? topExpanded : topCollapsed, false);
});

/* ── TOUCH DRAG ── */
let dragStartY   = 0;
let dragStartTop = 0;
let dragging     = false;

handle.addEventListener('touchstart', e => {
    dragging     = true;
    dragStartY   = e.touches[0].clientY;
    dragStartTop = currentTop;
    sheet.style.transition = 'none';
}, { passive: true });

window.addEventListener('touchmove', e => {
    if (!dragging) return;
    const dy  = e.touches[0].clientY - dragStartY;
    let newTop = dragStartTop + dy;
    newTop = Math.max(topExpanded - 30, Math.min(topCollapsed + 30, newTop));
    sheet.style.top = newTop + 'px';
    currentTop = newTop;
}, { passive: true });

window.addEventListener('touchend', () => {
    if (!dragging) return;
    dragging = false;
    snapSheet();
});

/* ── MOUSE DRAG (desktop preview) ── */
handle.addEventListener('mousedown', e => {
    dragging     = true;
    dragStartY   = e.clientY;
    dragStartTop = currentTop;
    sheet.style.transition = 'none';
    e.preventDefault();
});

window.addEventListener('mousemove', e => {
    if (!dragging) return;
    const dy   = e.clientY - dragStartY;
    let newTop = dragStartTop + dy;
    newTop     = Math.max(topExpanded - 30, Math.min(topCollapsed + 30, newTop));
    sheet.style.top = newTop + 'px';
    currentTop = newTop;
});

window.addEventListener('mouseup', () => {
    if (!dragging) return;
    dragging = false;
    snapSheet();
});

/* ── TAP HANDLE untuk toggle ── */
handle.addEventListener('click', (e) => {
    if (Math.abs(currentTop - dragStartTop) > 10) return;
    isExpanded = !isExpanded;
    applyTop(isExpanded ? topExpanded : topCollapsed, true);
    scroll.style.overflowY = isExpanded ? 'auto' : 'hidden';
});

/* ── CEGAH SCROLL KONTEN SAAT COLLAPSED ── */
scroll.addEventListener('touchstart', e => {
    if (!isExpanded) e.preventDefault();
}, { passive: false });

/* ── DROPDOWN USER ── */
function toggleUserMenu() {
    document.getElementById('userDropdown').classList.toggle('hidden');
}

function handleLogout() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/logout';
    const csrf = document.createElement('input');
    csrf.type  = 'hidden';
    csrf.name  = '_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
}

document.addEventListener('click', e => {
    const wrapper = document.getElementById('userMenuWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('userDropdown')?.classList.add('hidden');
    }
});

/* Mulai */
initSheet();
</script>

</body>
</html>