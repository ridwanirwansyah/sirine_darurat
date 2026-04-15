<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Import model User

class ProfileController extends Controller
{
    // Tampilkan halaman profil user
    public function index()
    {
        return view('user.profile', [
            'user' => Auth::user()
        ]);
    }

    // Update nomor telepon user
    public function updatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string|max:20'
        ]);

        /** @var User $user */
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 401);
        }

        $user->phone = $request->phone;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Nomor berhasil diperbarui'
        ]);
    }
}