<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Profil - Sisirin'e</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --app-height: 100dvh;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: #0f172a;
            height: var(--app-height);
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .app-shell {
            width: 100%;
            max-width: 480px;
            height: var(--app-height);
            display: flex;
            flex-direction: column;
            background: #0f172a;
            position: relative;
            overflow: hidden;
        }

        /* ── HEADER AREA (biru tua) ── */
        .top-area {
            flex-shrink: 0;
            padding: 16px 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .page-title {
            color: white;
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 20px;
            align-self: center;
            text-align: center;
        }

        /* ── AVATAR ── */
        .avatar-wrap {
            width: 104px;
            height: 104px;
            background: white;
            border-radius: 50%;
            padding: 5px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 5;
            margin-bottom: -52px;
            flex-shrink: 0;
        }

        .avatar-inner {
            width: 100%;
            height: 100%;
            background: #0f172a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-inner i {
            color: white;
            font-size: 2.8rem;
        }

        /* ── PROFILE CARD (putih) ── */
        .profile-card {
            flex: 1;
            background: white;
            border-top-left-radius: 32px;
            border-top-right-radius: 32px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 62px 22px 12px;
            min-height: 0;
        }

        /* Nama user */
        .user-name {
            text-align: center;
            margin-bottom: 20px;
            flex-shrink: 0;
        }

        .user-name h2 {
            font-size: 1.45rem;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        /* Info rows */
        .info-list {
            flex-shrink: 1;
            flex-grow: 0;
            display: flex;
            flex-direction: column;
            gap: 0;
            margin-bottom: 12px;
            overflow: hidden;
            min-height: 0;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .info-text {
            flex: 1;
            min-width: 0;
        }

        .info-label {
            font-size: 9px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .info-value.role {
            color: #dc2626;
            font-weight: 800;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Logout button */
        .logout-area {
            flex-shrink: 0;
            margin-top: 20px;
            padding: 0;
        }

        .btn-logout {
            width: 100%;
            background: #0f172a;
            color: white;
            border: none;
            border-radius: 100px;
            padding: 14px 24px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.3px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: opacity 0.15s;
        }

        .btn-logout:active {
            opacity: 0.8;
            transform: scale(0.98);
        }

        #pinToggleBtn:hover {
            background: #ede9fe !important;
        }

        #pinToggleBtn:active {
            transform: scale(0.9);
        }

        /* ── NAV (sama persis dengan dashboard) ── */
        .nav-wrapper {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            padding-bottom: calc(15px + env(safe-area-inset-bottom));
            background: linear-gradient(to top, white 95%, transparent);
            z-index: 40;
            pointer-events: none;
        }

        .nav-bar {
            background: #0f172a;
            border-radius: 100px;
            height: 68px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 0 8px;
            pointer-events: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .nav-bar button {
            transition: all 0.2s ease;
        }

        .nav-bar button:active {
            transform: scale(0.95);
        }

        /* Responsif layar kecil (tinggi < 680px) */
        @media (max-height: 680px) {
            .top-area {
                padding: 10px 20px 0;
            }

            .page-title {
                font-size: 1rem;
                margin-bottom: 12px;
            }

            .avatar-wrap {
                width: 78px;
                height: 78px;
                margin-bottom: -39px;
            }

            .avatar-inner i {
                font-size: 2rem;
            }

            .profile-card {
                padding-top: 46px;
                padding-bottom: 8px;
                border-radius: 24px 24px 0 0;
            }

            .user-name {
                margin-bottom: 10px;
            }

            .user-name h2 {
                font-size: 1.1rem;
            }

            .info-list {
                margin-bottom: 8px;
            }

            .info-row {
                padding: 7px 0;
            }

            .info-icon {
                width: 28px;
                height: 28px;
                font-size: 10px;
                border-radius: 7px;
            }

            .info-label {
                font-size: 8px;
            }

            .info-value {
                font-size: 11px;
            }

            .logout-area {
                padding: 6px 0 0;
            }

            .btn-logout {
                padding: 11px 20px;
                font-size: 11px;
            }

            .nav-wrapper {
                padding: 6px 20px;
                padding-bottom: calc(10px + env(safe-area-inset-bottom));
            }

            .nav-bar {
                height: 56px;
            }
        }

        /* Sangat kecil (tinggi < 580px) */
        @media (max-height: 580px) {
            .info-row {
                padding: 5px 0;
            }

            .info-icon {
                width: 24px;
                height: 24px;
                font-size: 9px;
            }

            .user-name h2 {
                font-size: 1rem;
            }

            .btn-logout {
                padding: 9px 16px;
                font-size: 10px;
            }
        }

        /* Layar besar (tinggi > 850px) */
        @media (min-height: 850px) {
            .avatar-wrap {
                width: 116px;
                height: 116px;
                margin-bottom: -58px;
            }

            .avatar-inner i {
                font-size: 3.2rem;
            }

            .profile-card {
                padding-top: 70px;
            }

            .user-name h2 {
                font-size: 1.6rem;
                margin-bottom: 24px;
            }

            .info-row {
                padding: 13px 0;
            }

            .info-icon {
                width: 38px;
                height: 38px;
                font-size: 15px;
            }

            .info-value {
                font-size: 14px;
            }

            .btn-logout {
                padding: 16px 24px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

    <div class="app-shell">

        <!-- AREA ATAS (biru tua) -->
        <div class="top-area">
            <h1 class="page-title">Profil Saya</h1>
            <!-- Avatar -->
            <div class="avatar-wrap">
                <div class="avatar-inner">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>
        </div>

        <!-- PROFILE CARD -->
        <div class="profile-card">

            <!-- Nama -->
            <div class="user-name">
                <h2>{{ $user->name ?? 'Ridwan' }}</h2>
            </div>

            <!-- Info rows -->
            <div class="info-list">
                <div class="info-row">
                    <div class="info-icon" style="background:#eff6ff;color:#2563eb">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-text">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $user->email ?? 'Belum diisi' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon" style="background:#f0fdf4;color:#16a34a">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-text">
                        <div class="info-label">No. Telepon</div>
                        <div style="display:flex;align-items:center;gap:8px;width:100%">

                            <!-- TEXT / INPUT -->
                            <input type="text"
                                id="phoneInput"
                                value="{{ $user->phone ?? '' }}"
                                disabled
                                class="info-value"
                                style="flex:1;border:none;background:transparent;outline:none" />

                            <!-- BUTTON EDIT -->
                            <button type="button" onclick="toggleEditPhone()" id="editBtn"
                                style="width:28px;height:28px;border-radius:8px;border:none;background:#eff6ff;color:#2563eb;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;">
                                <i class="fas fa-pen"></i>
                            </button>

                            <!-- BUTTON SAVE -->
                            <button type="button" onclick="savePhone()" id="saveBtn"
                                style="display:none;width:28px;height:28px;border-radius:8px;border:none;background:#16a34a;color:white;cursor:pointer;align-items:center;justify-content:center;font-size:12px;">
                                <i class="fas fa-check"></i>
                            </button>

                        </div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon" style="background:#fff1f2;color:#dc2626">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="info-text">
                        <div class="info-label">Role</div>
                        <div class="info-value role">{{ isset($user) ? ucfirst($user->role) : 'User' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon" style="background:#fefce8;color:#ca8a04">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="info-text">
                        <div class="info-label">Bergabung</div>
                        <div class="info-value">
                            {{ isset($user) ? $user->created_at->locale('id')->translatedFormat('d M Y') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon" style="background:#f5f3ff;color:#7c3aed">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="info-text">
                        <div class="info-label">PIN</div>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="info-value" id="pinDisplay"
                                style="font-family:monospace;letter-spacing:4px;flex:1"
                                data-pin="{{ isset($user) && $user->pin ? $user->pin : '' }}"
                                data-masked="{{ isset($user) && $user->pin ? str_repeat('•', strlen($user->pin)) : 'Belum diatur' }}">
                                {{ isset($user) && $user->pin ? str_repeat('•', strlen($user->pin)) : 'Belum diatur' }}
                            </div>
                            @if(isset($user) && $user->pin)
                            <button type="button" id="pinToggleBtn" onclick="togglePin()"
                                style="width:28px;height:28px;border-radius:8px;border:none;background:#f5f3ff;color:#7c3aed;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;transition:background 0.15s">
                                <i class="fas fa-eye" id="pinToggleIcon"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logout -->
            <div class="logout-area">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>

        </div><!-- /profile-card -->

        <!-- NAV (SAMA PERSIS SEPERTI DASHBOARD) -->
        <div class="nav-wrapper">
            <div class="nav-bar">
                <button onclick="location.href='/user/dashboard'"
                    class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition shrink-0">
                    <i class="fa-solid fa-house"></i>
                </button>
                <button onclick="location.href='/user/incidents/create'"
                    class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition shrink-0">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                </button>
                <button onclick="location.href='/user/incidents'"
                    class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition shrink-0">
                    <i class="fa-solid fa-clipboard-list"></i>
                </button>
                <button onclick="location.href='/user/riwayat'"
                    class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition shrink-0">
                    <i class="fa-solid fa-list-ul"></i>
                </button>
                <button onclick="location.href='/user/profile'"
                    class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-red-500 text-2xl shadow-xl -mt-6 border-[6px] border-[#0f172a] shrink-0">
                    <i class="fa-solid fa-user"></i>
                </button>
            </div>
        </div>

    </div>

    <script>
        // Stabilkan tinggi viewport (address bar mobile)
        function setAppHeight() {
            document.documentElement.style.setProperty('--app-height', window.innerHeight + 'px');
        }
        window.addEventListener('resize', setAppHeight);
        window.addEventListener('orientationchange', setAppHeight);
        setAppHeight();

        /* ── TOGGLE PIN ── */
        let pinVisible = false;
        let editingPhone = false;

        function togglePin() {
            const display = document.getElementById('pinDisplay');
            const icon = document.getElementById('pinToggleIcon');
            if (!display) return;

            pinVisible = !pinVisible;

            if (pinVisible) {
                display.textContent = display.dataset.pin || '—';
                display.style.letterSpacing = '4px';
                icon.className = 'fas fa-eye-slash';
            } else {
                display.textContent = display.dataset.masked || '••••••';
                icon.className = 'fas fa-eye';
            }
        }

        function savePhone() {
            const input = document.getElementById('phoneInput');
            const value = input.value.trim();

            if (value && !/^[0-9+\-\s]{8,15}$/.test(value)) {
                alert('Format nomor telepon tidak valid (min 8 digit, maks 15)');
                return;
            }

            fetch('{{ route("user.profile.update-phone") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ phone: value })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
                    }
                })
                .catch((err) => {
                    console.error(err);
                    alert('Terjadi kesalahan jaringan');
                });
        }

        function toggleEditPhone() {
            const input = document.getElementById('phoneInput');
            const editBtn = document.getElementById('editBtn');
            const saveBtn = document.getElementById('saveBtn');

            input.disabled = false;
            input.focus();

            editBtn.style.display = 'none';
            saveBtn.style.display = 'flex';
        }
    </script>
</body>

</html>