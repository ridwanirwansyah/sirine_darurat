<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Tampilkan semua notifikasi
     */
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        $unreadCount = Notification::where('user_id', auth()->id())
            ->unread()
            ->count();
            
        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }
    
    /**
     * Tandai semua notifikasi sebagai sudah dibaca
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        return response()->json([
            'status' => 'success',
            'message' => 'Semua notifikasi telah ditandai sebagai sudah dibaca'
        ]);
    }
    
    /**
     * Hapus notifikasi
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        
        // Pastikan hanya pemilik yang bisa menghapus
        if ($notification->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak berhak menghapus notifikasi ini'
            ], 403);
        }
        
        $notification->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi berhasil dihapus'
        ]);
    }
}