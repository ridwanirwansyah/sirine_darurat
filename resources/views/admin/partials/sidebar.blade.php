{{-- resources/views/admin/partials/sidebar.blade.php --}}
{{-- MOBILE MENU BUTTON --}}
<button id="mobile-menu-btn" class="mobile-menu-btn">
    <i class="fas fa-bars text-xl"></i>
</button>

{{-- BACKDROP UNTUK MENUTUP SIDEBAR --}}
<div id="sidebarBackdrop" class="sidebar-backdrop"></div>

<div class="sidebar w-64 text-white flex flex-col">

    {{-- LOGO SECTION --}}
    <div class="p-6">
        <div class="flex items-center space-x-3">
            <div class="logo-container w-12 h-12 flex items-center justify-center">
                <i class="fas fa-bullhorn text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold">Sisirine™</h1>
                <p class="text-sm text-blue-100">Sistem Informasi Sirine Sekolah</p>
            </div>
        </div>

        {{-- WELCOME SECTION --}}
        <div class="mt-4 pt-4 border-t border-blue-400">
            <h2 class="font-semibold">Selamat Datang Admin!</h2>
            <div class="mt-3 flex items-center text-blue-100">
                <i class="far fa-clock mr-2"></i>
                <span id="current-time"></span>
            </div>
        </div>
    </div>

    {{-- NAVIGATION MENU --}}
    <nav class="px-3 flex-1">
        <h3 class="text-xs uppercase tracking-wider text-blue-300 font-semibold mb-3 px-1">Menu Utama</h3>
        <ul>

            {{-- DASHBOARD --}}
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" 
                   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active-menu' : '' }}">
                    <i class="fas fa-tachometer-alt mr-3 w-5"></i>
                    <span>Beranda</span>
                </a>
            </li>

            {{-- MANAJEMEN SIRINE --}}
            <li class="nav-item">
                <a href="{{ route('admin.sirine') }}" 
                   class="nav-link {{ request()->routeIs('admin.sirine') ? 'active-menu' : '' }}">
                    <i class="fas fa-bullhorn mr-3 w-5"></i>
                    <span>Manajemen Sirine</span>
                </a>
            </li>

            {{-- LAPORAN KEJADIAN --}}
            <li class="nav-item">
                <a href="{{ route('admin.incidents.index') }}" 
                   class="nav-link {{ request()->routeIs('admin.incidents.*') ? 'active-menu' : '' }}">
                    <i class="fas fa-exclamation-triangle mr-3 w-5"></i>
                    <span>Laporan Kejadian</span>
                </a>
            </li>

            {{-- MANAJEMEN AKUN --}}
            <li class="nav-item">
                <a href="{{ route('admin.pengguna') }}" 
                   class="nav-link {{ request()->routeIs('admin.pengguna') ? 'active-menu' : '' }}">
                    <i class="fas fa-user-cog mr-3 w-5"></i>
                    <span>Manajemen Akun</span>
                </a>
            </li>

            {{-- LOG AKTIVITAS / RIWAYAT --}}
            <li class="nav-item">
                <a href="{{ route('admin.riwayat') }}" 
                   class="nav-link {{ request()->routeIs('admin.riwayat') ? 'active-menu' : '' }}">
                    <i class="fas fa-history mr-3 w-5"></i>
                    <span>Riwayat Aktivitas</span>
                </a>
            </li>

            {{-- PENGATURAN --}}
            <li class="nav-item">
                <a href="{{ route('admin.pengaturan') }}" 
                   class="nav-link {{ request()->routeIs('admin.pengaturan') ? 'active-menu' : '' }}">
                    <i class="fas fa-cog mr-3 w-5"></i>
                    <span>Pengaturan</span>
                </a>
            </li>

        </ul>
    </nav>

    {{-- SIDEBAR FOOTER --}}
    <div class="p-3 border-t border-blue-400 space-y-3">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center py-2 px-3 bg-red-500 hover:bg-red-600 rounded text-white text-sm font-medium transition">
                <i class="fas fa-sign-out-alt mr-1"></i> Keluar
            </button>
        </form>
        <div class="text-center text-xs text-blue-200 pt-2 border-t border-blue-400">
            <p>© 2026 Sisirine™</p>
        </div>
    </div>

</div>