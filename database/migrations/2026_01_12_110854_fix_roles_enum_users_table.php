<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan ENUM baru 'user' dan 'admin' TANPA menghapus yang lama
        DB::statement("
            ALTER TABLE users MODIFY role 
            ENUM('murid','wakasek','satpam','sapras','user','admin') 
            NOT NULL DEFAULT 'user'
        ");

        // 2. Update semua role lama menjadi 'user'
        DB::table('users')
            ->whereIn('role', ['murid', 'wakasek', 'satpam', 'sapras'])
            ->update(['role' => 'user']);

        // 3. Hapus ENUM lama, sisakan hanya 'user' dan 'admin'
        DB::statement("
            ALTER TABLE users MODIFY role 
            ENUM('user','admin') 
            NOT NULL DEFAULT 'user'
        ");
    }

    public function down(): void
    {
        // Kembalikan ENUM lama jika rollback
        DB::statement("
            ALTER TABLE users MODIFY role 
            ENUM('murid','wakasek','satpam','sapras') 
            NOT NULL DEFAULT 'murid'
        ");
    }
};
