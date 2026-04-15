<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            // Jika field details belum ada
            if (!Schema::hasColumn('alarm_logs', 'details')) {
                $table->json('details')->nullable()->after('event_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            $table->dropColumn('details');
        });
    }
};