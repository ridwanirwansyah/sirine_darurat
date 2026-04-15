{{-- resources/views/admin/partials/header.blade.php --}}
<header class="bg-white shadow-sm py-4 px-6 header-sticky">
    <div class="flex justify-between items-center">
        {{-- Bagian Kiri: Judul Halaman --}}
        <div>
            <h2 class="text-lg md:text-xl font-bold text-gray-800">{{ $pageTitle ?? 'Dashboard' }}</h2>
            <p class="text-xs md:text-sm text-gray-600">{{ $pageDescription ?? 'Overview sistem dan aktivitas terbaru' }}</p>
        </div>

        {{-- Bagian Kanan: Ikon Lonceng & Profile Dropdown --}}
        <div class="flex items-center space-x-4 md:space-x-6">
            {{-- NOTIFICATIONS --}}
            <button class="relative text-gray-500 hover:text-gray-700 transition">
                <i class="fas fa-bell text-lg md:text-xl"></i>
                @php
                    $unreadCount = \App\Models\Notification::where('user_id', auth()->id())->unread()->count();
                @endphp
                @if($unreadCount > 0)
                <span class="absolute -top-1 -right-2 bg-red-500 text-xs w-4 h-4 md:w-5 md:h-5 flex items-center justify-center text-white rounded-full font-bold">
                    {{ $unreadCount }}
                </span>
                @endif
            </button>

            {{-- PROFILE DROPDOWN --}}
            <div class="profile-dropdown">
                <div id="profileBtn" class="flex items-center space-x-2 md:space-x-3 cursor-pointer">
                    <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600 text-sm md:text-base"></i>
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-medium text-gray-800">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-gray-500">Super Admin</p>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-xs hidden sm:block"></i>
                </div>

                {{-- DROPDOWN MENU --}}
                <div id="dropdownMenu" class="dropdown-menu">
                    <div class="dropdown-header">
                        <p class="font-semibold text-gray-800 text-sm">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ auth()->user()->email ?? 'admin@sisirine.com' }}</p>
                    </div>
                    
                    <a href="{{ route('admin.pengaturan') }}" class="dropdown-item">
                        <i class="fas fa-user-cog"></i>
                        <span>Pengaturan Akun</span>
                    </a>
                    
                    <div class="dropdown-divider"></div>
                    
                    <button id="logoutDropdownBtn" class="dropdown-item logout-item w-full text-left">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Keluar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- FORM LOGOUT TERSEMBUNYI --}}
<form id="logoutForm" method="POST" action="{{ route('logout') }}" style="display: none;">
    @csrf
</form>