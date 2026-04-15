<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil PIN langsung dari database agar tidak terblokir $hidden
        $userPin = DB::table('users')->where('id', $user->id)->value('pin') ?? '';

        // Cek apakah user punya insiden aktif
        $hasActiveIncident = Incident::active()
            ->where('user_id', $user->id)
            ->exists();

        // Get latest active incident for display
        $latestIncident = null;
        if ($hasActiveIncident) {
            $latestIncident = Incident::active()
                ->where('user_id', $user->id)
                ->latest('reported_at')
                ->first();
        }

        return view('user.dashboard', [
            'user'               => $user,
            'userPin'            => $userPin,
            'hasActiveIncident'  => $hasActiveIncident,
            'latestIncident'     => $latestIncident,
        ]);
    }
}