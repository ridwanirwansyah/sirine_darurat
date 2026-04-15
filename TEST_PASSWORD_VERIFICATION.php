#!/usr/bin/env php
<?php
/**
 * Script: Test Password Verification After Import
 * Usage: php artisan tinker < test_password_verification.php
 * Or: php artisan tinker (then copy-paste commands)
 */

echo "=== PASSWORD VERIFICATION GUIDE ===\n\n";

// ============================================
// CARA 1: Test Password di Tinker
// ============================================
echo "CARA 1: TEST PASSWORD DI TINKER\n";
echo "================================\n";
echo "Buka: php artisan tinker\n";
echo "Lalu jalankan commands:\n\n";

$commands = [
    "// 1. Cek user baru yang di-import",
    "\$user = App\\Models\\User::where('email', 'john@example.com')->first();",
    "\$user;  // Lihat user data",
    "",
    "// 2. Test password dengan default password",
    "Hash::check('Password123!', \$user->password);",
    "// Result: true (password benar) atau false (salah)",
    "",
    "// 3. Test password yang salah",
    "Hash::check('SalahPassword', \$user->password);",
    "// Result: false (password salah)",
    "",
    "// 4. Lihat semua user baru 1 jam terakhir",
    "App\\Models\\User::where('created_at', '>=', now()->subHour())->get();",
    "",
    "// 5. Count total user yang di-import",
    "App\\Models\\User::count();",
];

foreach ($commands as $cmd) {
    echo "  " . $cmd . "\n";
}

// ============================================
// CARA 2: Test Login Langsung
// ============================================
echo "\n\nCARA 2: TEST LOGIN LANGSUNG DI WEB\n";
echo "==================================\n";
echo "1. Buka: http://localhost/login\n";
echo "2. Email: john@example.com (ganti dengan email user import)\n";
echo "3. Password: Password123!\n";
echo "4. Klik Login\n";
echo "5. Jika berhasil → Password OK ✅\n";

// ============================================
// CARA 3: Database Check
// ============================================
echo "\n\nCARA 3: DATABASE CHECK\n";
echo "======================\n";
echo "Jalankan SQL query:\n\n";

$sqlQueries = [
    "-- Lihat user yang baru di-import (1 jam terakhir)",
    "SELECT id, name, email, role, is_active, created_at",
    "FROM users",
    "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
    "ORDER BY created_at DESC;",
    "",
    "-- Lihat password hash user (encrypted)",
    "SELECT id, name, email, password FROM users",
    "WHERE email = 'john@example.com';",
    "",
    "-- Count berapa user yang di-import",
    "SELECT COUNT(*) as total_users FROM users;",
];

foreach ($sqlQueries as $query) {
    echo "  " . $query . "\n";
}

// ============================================
// CARA 4: Reset Password jika Lupa
// ============================================
echo "\n\nCARA 4: RESET PASSWORD JIKA LUPA\n";
echo "================================\n";
echo "Buka: php artisan tinker\n\n";

$resetCommands = [
    "// Cari user",
    "\$user = App\\Models\\User::where('email', 'john@example.com')->first();",
    "",
    "// Reset ke default password",
    "\$user->password = Hash::make('Password123!');",
    "\$user->save();",
    "",
    "// Atau set ke password baru",
    "\$user->password = Hash::make('NewPassword123');",
    "\$user->save();",
    "",
    "// Verify password sudah di-reset",
    "Hash::check('NewPassword123', \$user->password);  // Return: true",
];

foreach ($resetCommands as $cmd) {
    echo "  " . $cmd . "\n";
}

// ============================================
// CARA 5: Bulk Check All Imported Users
// ============================================
echo "\n\nCARA 5: BULK CHECK SEMUA USER IMPORT\n";
echo "====================================\n";
echo "Buka: php artisan tinker\n\n";

$bulkCommands = [
    "// Get semua user yang baru di-import",
    "\$users = App\\Models\\User::where('created_at', '>=', now()->subHour())->get();",
    "",
    "// Loop untuk verify password masing-masing",
    "\$users->each(function(\$user) {",
    "    \$passwordOk = Hash::check('Password123!', \$user->password);",
    "    echo \$user->email . ' → ' . (\$passwordOk ? '✅ OK' : '❌ FAIL') . \"\\n\";",
    "});",
];

foreach ($bulkCommands as $cmd) {
    echo "  " . $cmd . "\n";
}

// ============================================
// Important Notes
// ============================================
echo "\n\n🔐 PENTING:\n";
echo "===========\n";
echo "1. Default Password: Password123!\n";
echo "2. Password disimpan sebagai HASH (encrypted) di database\n";
echo "3. Tidak bisa lihat password plain text di database\n";
echo "4. Hash::check() untuk verify password\n";
echo "5. Hash::make() untuk hash password baru\n";
echo "6. User wajib change password saat login pertama\n";

// ============================================
// Troubleshooting
// ============================================
echo "\n\n🔧 TROUBLESHOOTING:\n";
echo "===================\n";

$troubleshoots = [
    "Q: User tidak bisa login?",
    "A: • Check apakah user terdaftar (cek di user list)",
    "   • Check email benar (tidak ada typo)",
    "   • Check status user = Aktif",
    "   • Try reset password pakai tinker",
    "",
    "Q: Password hash tidak cocok?",
    "A: • Hash::check() seharusnya return true",
    "   • Jika false, coba reset password",
    "   • Jika tetap false, ada masalah di hashing",
    "",
    "Q: Bagaimana cek password yang benar?",
    "A: • Tinker: Hash::check('Password123!', \$user->password)",
    "   • Login ke web: pakai email dan password",
    "   • Database: lihat password hash (encrypted)",
];

foreach ($troubleshoots as $line) {
    echo "  " . $line . "\n";
}

echo "\n✅ Done!\n";
?>
