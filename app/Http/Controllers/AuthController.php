<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AlarmLog;

class AuthController extends Controller
{
    // Tampilkan halaman login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {

            $user = auth()->user();

            if (!$user->is_active) {
                Auth::logout();

                return back()->withErrors([
                    'auth' => 'Akun Anda tidak aktif. Silakan hubungi Admin Sistem.',
                ]);
            }

            $request->session()->regenerate();

            // ✅ LOG: Catat aktivitas login
            AlarmLog::record([
                'action' => 'LOGIN',
                'target_type' => 'Auth',
                'target_id' => $user->id,
                'user_name' => $user->name,
                'description' => "User {$user->name} ({$user->email}) berhasil login ke sistem",
                'details' => [
                    'email' => $user->email,
                    'role' => $user->role,
                    'login_time' => now()->toDateTimeString(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            ]);

            // ------------------------------------------------------------------------------------
            // Redirect Berdasarkan Role
            // ------------------------------------------------------------------------------------
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('user.dashboard');
        }

        // ❌ LOG: Catat percobaan login gagal
        AlarmLog::record([
            'action' => 'LOGIN_FAILED',
            'target_type' => 'Auth',
            'description' => "Percobaan login gagal untuk email: {$request->email}",
            'details' => [
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'failed_at' => now()->toDateTimeString()
            ]
        ]);

        return back()->withErrors([
            'auth' => 'Email atau kata sandi yang Anda masukkan salah.',
        ]);
    }

    // Proses logout
    public function logout(Request $request)
    {
        $user = auth()->user();
        
        // ✅ LOG: Catat aktivitas logout
        if ($user) {
            AlarmLog::record([
                'action' => 'LOGOUT',
                'target_type' => 'Auth',
                'target_id' => $user->id,
                'user_name' => $user->name,
                'description' => "User {$user->name} ({$user->email}) logout dari sistem",
                'details' => [
                    'email' => $user->email,
                    'role' => $user->role,
                    'logout_time' => now()->toDateTimeString(),
                    'ip_address' => $request->ip()
                ]
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }
}