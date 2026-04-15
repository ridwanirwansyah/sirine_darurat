<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * Show settings page
     */
    public function index()
    {
        $settings = [
            'auto_off' => Setting::getSetting('auto_off', false),
            'auto_off_time' => Setting::getSetting('auto_off_time', null),
            'notification_realtime' => Setting::getSetting('notification_realtime', true),
            'safe_mode' => Setting::getSetting('safe_mode', true),
            'app_theme' => Setting::getSetting('app_theme', 'blue'),
            'font_size' => Setting::getSetting('font_size', 'default'),
        ];

        return view('admin.pengaturan', compact('settings'));
    }

    /**
     * Save system settings
     */
    public function saveSystemSettings(Request $request)
    {
        $request->validate([
            'auto_off' => 'boolean',
            'auto_off_time' => 'nullable|date_format:Y-m-d\TH:i',
            'notification_realtime' => 'boolean',
            'safe_mode' => 'boolean',
        ]);

        Setting::setSetting('auto_off', $request->auto_off, 'boolean', 'Auto-OFF Sirine aktif');
        Setting::setSetting('auto_off_time', $request->auto_off_time, 'string', 'Jadwal Auto-OFF');
        Setting::setSetting('notification_realtime', $request->notification_realtime, 'boolean', 'Notifikasi Real-time');
        Setting::setSetting('safe_mode', $request->safe_mode, 'boolean', 'Mode Aman');

        return response()->json([
            'status' => 'ok',
            'message' => 'Pengaturan sistem berhasil disimpan',
        ]);
    }

    /**
     * Save app settings
     */
    public function saveAppSettings(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:blue,dark,light,red,green',
            'font_size' => 'required|in:small,default,large',
        ]);

        Setting::setSetting('app_theme', $request->theme, 'string', 'Tema Aplikasi');
        Setting::setSetting('font_size', $request->font_size, 'string', 'Ukuran Teks');

        return response()->json([
            'status' => 'ok',
            'message' => 'Pengaturan aplikasi berhasil disimpan',
        ]);
    }

    /**
     * Update admin account
     */
    public function updateAccount(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'old_password' => 'nullable|required_if:new_password,!=null',
            'new_password' => 'nullable|min:6|confirmed',
        ], [
            'new_password.confirmed' => 'Konfirmasi password tidak sesuai',
            'old_password.required_if' => 'Password lama harus diisi untuk mengubah password',
        ]);

        // Check old password if trying to change password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Password lama tidak sesuai',
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);
        }

        // Update name and email
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Akun berhasil diperbarui',
            'user' => $user,
        ]);
    }

    /**
     * Get current settings
     */
    public function getSettings()
    {
        $settings = [
            'auto_off' => Setting::getSetting('auto_off', false),
            'auto_off_time' => Setting::getSetting('auto_off_time', null),
            'notification_realtime' => Setting::getSetting('notification_realtime', true),
            'safe_mode' => Setting::getSetting('safe_mode', true),
            'app_theme' => Setting::getSetting('app_theme', 'blue'),
            'font_size' => Setting::getSetting('font_size', 'default'),
        ];

        return response()->json([
            'status' => 'ok',
            'settings' => $settings,
        ]);
    }
}
