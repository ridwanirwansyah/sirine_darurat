<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Sisirin'e</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        html { 
            -webkit-text-size-adjust: 100%; 
        }
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        @media (max-width: 768px) { 
            input { font-size: 16px !important; }
        }
        @keyframes muncul {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes tampil {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .animasi-muncul {
            animation: muncul 0.3s ease-out;
        }
        .animasi-tampil {
            animation: tampil 0.3s ease-out;
        }
        .efek-kaca {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .input-fokus:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .tombol-lihat-password {
            cursor: pointer;
            transition: all 0.2s;
        }
        .tombol-lihat-password:hover {
            color: #3b82f6;
        }
    </style>
</head>

<body class="text-slate-900 font-sans antialiased">

{{-- ================= MODAL ERROR ================= --}}
@if ($errors->has('auth'))
<div 
    id="modalError"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4 animasi-muncul"
>
    <div class="efek-kaca w-full max-w-sm rounded-3xl shadow-2xl p-8 relative animasi-tampil">

        <button 
            onclick="document.getElementById('modalError').remove()"
            class="absolute top-5 right-5 text-slate-400 hover:text-slate-700 transition-all text-2xl w-10 h-10 flex items-center justify-center rounded-full hover:bg-slate-100"
        >
            &times;
        </button>

        <div class="flex justify-center mb-5">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-red-100 to-red-50 flex items-center justify-center shadow-lg">
                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" stroke-width="2.5"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
        </div>

        <h2 class="text-2xl font-bold text-center text-slate-900 mb-3">
            Gagal Masuk
        </h2>

        <p class="text-center text-slate-600 text-sm mb-6">
            {{ $errors->first('auth') }}
        </p>

        <button 
            onclick="document.getElementById('modalError').remove()"
            class="w-full py-3.5 bg-gradient-to-r from-slate-900 to-slate-800 text-white font-semibold rounded-xl hover:from-slate-800 hover:to-slate-700 transition-all shadow-lg"
        >
            Tutup
        </button>
    </div>
</div>
@endif
{{-- ================= END MODAL ================= --}}

<div class="min-h-screen flex flex-col items-center justify-center px-4 py-8 relative z-10">

    <div class="w-full max-w-md animasi-muncul">

        <div class="efek-kaca rounded-3xl shadow-2xl p-8 animasi-tampil">
            
            <div class="text-center mb-6">
                <img src="{{ asset('image/logo2.png') }}" class="w-28 mx-auto" alt="Logo Sisirin'e">
            </div>

            <div class="text-center mb-6 border-b border-slate-200 pb-4">
                <h2 class="text-xl font-bold text-slate-900 mb-2">Selamat Datang</h2>
                <p class="text-slate-500 text-sm">
                    Masukkan data untuk melanjutkan
                </p>
            </div>

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 ml-1">
                        Alamat Email
                    </label>
                    <input
                        type="email"
                        name="email"
                        placeholder="nama@email.com"
                        required
                        class="w-full mt-2 px-4 py-4 rounded-xl border-2 border-slate-200 focus:border-blue-500 input-fokus"
                    >
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 ml-1">
                        Kata Sandi
                    </label>
                    <div class="relative mt-2">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            class="w-full px-4 py-4 rounded-xl border-2 border-slate-200 focus:border-blue-500 input-fokus"
                        >
                        <button 
                            type="button"
                            onclick="togglePassword()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 tombol-lihat-password"
                        >
                            <i class="fas fa-eye text-slate-400" id="ikonMata"></i>
                        </button>
                    </div>
                </div>

                {{-- Hubungi Admin (SUDAH KE WA) --}}
                <div class="flex items-center justify-end">
                    <p class="text-sm text-slate-600">
                        Lupa kata sandi? 
                        <a 
                            href="https://wa.me/6283829883207"
                            target="_blank"
                            class="font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                        >
                            Hubungi Admin
                        </a>
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg"
                >
                    MASUK
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-slate-500">
                Sistem ini hanya untuk pengguna yang memiliki akses
            </div>
        </div>

        <div class="mt-6 text-center text-sm text-blue-100">
            © 2026 Sisirin'e. Hak cipta dilindungi.
        </div>

    </div>

</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const ikon = document.getElementById('ikonMata');

    if (input.type === 'password') {
        input.type = 'text';
        ikon.classList.replace('fa-eye', 'fa-eye-slash');
        ikon.classList.add('text-blue-500');
    } else {
        input.type = 'password';
        ikon.classList.replace('fa-eye-slash', 'fa-eye');
        ikon.classList.remove('text-blue-500');
    }
}
</script>

</body>
</html>