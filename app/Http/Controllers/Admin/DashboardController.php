<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AlarmLog;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ========== STATISTIK UTAMA ==========
        $totalUsers = User::where('role', 'user')->count();
        
        // 2. Total ALARM_ON (sirine dinyalakan)
        $totalAlarmOn = AlarmLog::where('action', 'ALARM_ON')->count();
        
        // 3. Total AUTO_OFF (sirine mati otomatis)
        $totalAlarmOff = AlarmLog::where('action', 'AUTO_OFF')->count();
        
        // ========== AKTIVITAS TERAKHIR (ALARM_ON dan AUTO_OFF) ==========
        $recentLogs = AlarmLog::with('user')
            ->whereIn('action', ['ALARM_ON', 'AUTO_OFF'])  // Hanya ALARM_ON dan AUTO_OFF
            ->orderBy('event_time', 'desc')
            ->limit(10)
            ->get();
        
        // ========== LAPORAN TERAKHIR ==========
        $recentIncidents = Incident::with('user')
            ->orderBy('reported_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('admin.dashboard', compact(
            'totalUsers',
            'totalAlarmOn',
            'totalAlarmOff',
            'recentLogs',
            'recentIncidents'
        ));
    }
}