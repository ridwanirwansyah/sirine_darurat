{{-- resources/views/admin/pengaturan.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengaturan - Sisirine™ Admin</title>

    {{-- Tailwind & Font Awesome --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Global Styles --}}
    @include('admin.partials.styles')
</head>
<body class="bg-gray-50">

    <div class="flex min-h-screen">
        {{-- SIDEBAR --}}
        @include('admin.partials.sidebar')

        {{-- MAIN CONTENT --}}
        <div class="flex-1 flex flex-col dashboard-content">

            {{-- HEADER --}}
            @include('admin.partials.header', [
                'pageTitle' => 'Pengaturan',
                'pageDescription' => 'Kelola pengaturan akun admin'
            ])

            {{-- MAIN CONTENT --}}
            <main class="main-scroll">

                {{-- PENGATURAN AKUN ADMIN SECTION --}}
                <section class="mb-6 md:mb-8">
                    <div class="bg-white rounded-xl shadow-md p-4 md:p-6 border border-gray-200 hover:shadow-lg transition">

                        <h3 class="text-base md:text-lg font-bold text-gray-800 mb-4 md:mb-6 flex items-center">
                            <i class="fas fa-user-shield mr-2 md:mr-3 text-blue-600"></i>
                            Pengaturan Akun Admin
                        </h3>

                        <form id="accountForm" class="space-y-4 md:space-y-6">

                            {{-- Nama --}}
                            <div>
                                <label class="block text-xs md:text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-user mr-2 text-blue-600"></i>
                                    Nama Lengkap
                                </label>
                                <input type="text" id="adminName" value="{{ auth()->user()->name ?? 'Admin' }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                                    placeholder="Masukkan nama lengkap">
                            </div>

                            {{-- Email --}}
                            <div>
                                <label class="block text-xs md:text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-envelope mr-2 text-blue-600"></i>
                                    Alamat Email
                                </label>
                                <input type="email" id="adminEmail" value="{{ auth()->user()->email ?? 'admin@sisirine.com' }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                                    placeholder="admin@example.com">
                            </div>

                            {{-- Divider --}}
                            <div class="border-t border-gray-200 pt-4">
                                <p class="text-xs md:text-sm font-semibold text-gray-700 mb-4">
                                    <i class="fas fa-lock mr-2 text-blue-600"></i>
                                    Ubah Kata Sandi
                                </p>
                            </div>

                            {{-- Kata Sandi Lama --}}
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Kata Sandi Lama</label>
                                <input type="password" id="oldPassword"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                                    placeholder="Masukkan kata sandi lama">
                            </div>

                            {{-- Kata Sandi Baru --}}
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Kata Sandi Baru</label>
                                <input type="password" id="newPassword"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                                    placeholder="Minimal 6 karakter">
                            </div>

                            {{-- Konfirmasi Kata Sandi Baru --}}
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" id="confirmPassword"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                                    placeholder="Ulangi kata sandi baru">
                            </div>

                            {{-- Simpan Button --}}
                            <div class="pt-4">
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center text-sm md:text-base">
                                    <i class="fas fa-save mr-2"></i>
                                    Simpan Perubahan
                                </button>
                            </div>

                        </form>

                    </div>
                </section>

            </main>

        </div>
    </div>

    {{-- Global Scripts --}}
    @include('admin.partials.scripts')

    {{-- JS KHUSUS PENGATURAN (Tidak Diubah) --}}
    <script>
        /**
         * Validate and save account settings
         */
        function initAccountForm() {
            const form = document.getElementById('accountForm');

            form.addEventListener('submit', (e) => {
                e.preventDefault();

                const name = document.getElementById('adminName').value.trim();
                const email = document.getElementById('adminEmail').value.trim();
                const oldPass = document.getElementById('oldPassword').value;
                const newPass = document.getElementById('newPassword').value;
                const confirmPass = document.getElementById('confirmPassword').value;

                // Validation
                if (!name || name.length < 3) {
                    showNotification('⚠️ Nama harus minimal 3 karakter!', 'error');
                    return;
                }

                if (!email || !email.includes('@')) {
                    showNotification('⚠️ Alamat email tidak valid!', 'error');
                    return;
                }

                // Password validation (only if user wants to change password)
                if (oldPass || newPass || confirmPass) {
                    if (!oldPass) {
                        showNotification('⚠️ Masukkan kata sandi lama!', 'error');
                        return;
                    }

                    if (newPass.length < 6) {
                        showNotification('⚠️ Kata sandi baru harus minimal 6 karakter!', 'error');
                        return;
                    }

                    if (newPass !== confirmPass) {
                        showNotification('⚠️ Kata sandi baru dan konfirmasi tidak cocok!', 'error');
                        return;
                    }
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                const payload = {
                    name: name,
                    email: email
                };

                if (newPass) {
                    payload.old_password = oldPass;
                    payload.new_password = newPass;
                    payload.new_password_confirmation = confirmPass;
                }

                fetch('/admin/pengaturan/akun', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'ok') {
                            showNotification('✅ ' + res.message, 'success');

                            // Clear password fields
                            document.getElementById('oldPassword').value = '';
                            document.getElementById('newPassword').value = '';
                            document.getElementById('confirmPassword').value = '';
                        } else {
                            showNotification('❌ ' + (res.message || 'Gagal memperbarui akun'), 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        showNotification('❌ Terjadi kesalahan', 'error');
                    });
            });
        }

        // Initialize page-specific functions
        document.addEventListener('DOMContentLoaded', () => {
            initAccountForm();
        });
    </script>
</body>
</html>