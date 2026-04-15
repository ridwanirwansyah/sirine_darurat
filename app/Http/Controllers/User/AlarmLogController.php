<?php

namespace App\Http\Controllers\User;

use App\Models\AlarmLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AlarmLogController extends Controller
{
    /**
     * Halaman riwayat aktivitas
     */
    public function index()
    {
        $user = Auth::user();

        /**
         * Untuk tahap awal:
         * - User hanya melihat log miliknya sendiri
         * - Nanti admin bisa melihat semua
         */
        $logs = AlarmLog::with('user')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.riwayat', compact('logs', 'user'));
    }

    
}
