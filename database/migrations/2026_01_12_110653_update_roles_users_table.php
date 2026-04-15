<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update semua role lama menjadi role baru
        DB::table('users')->whereIn('role', ['murid', 'wakasek', 'satpam', 'sapras'])
            ->update(['role' => 'user']);

        // 2. Baru ubah struktur enum menjadi user/admin
        DB::statement("ALTER TABLE users MODIFY role ENUM('user', 'admin') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        // Jika rollback, kembalikan enum ke role lama (opsional)
        DB::statement("
            ALTER TABLE users MODIFY role 
            ENUM('murid','wakasek','satpam','sapras','user','admin') 
            NOT NULL DEFAULT 'murid'
        ");
    }
};
