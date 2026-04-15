<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('unique_id', 10)->unique()->nullable()->after('id');
            $table->string('pin', 6)->nullable()->after('password');
        });

        // Generate unique_id dan pin untuk user yang sudah ada
        $users = DB::table('users')->get();
        $usedIds = [];
        
        foreach ($users as $user) {
            // Generate unique_id 2 digit yang unik
            do {
                $uniqueId = str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
            } while (in_array($uniqueId, $usedIds));
            
            $usedIds[] = $uniqueId;
            
            // Generate PIN 6 digit
            $pin = sprintf("%06d", mt_rand(1, 999999));
            
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'unique_id' => $uniqueId,
                    'pin' => $pin
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['unique_id', 'pin']);
        });
    }
};