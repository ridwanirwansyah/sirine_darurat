<?php
// database/migrations/2024_01_15_000002_update_alarm_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('alarm_logs', 'session_id')) {
                $table->string('session_id')->nullable()->after('action');
            }
            if (!Schema::hasColumn('alarm_logs', 'trigger_source')) {
                $table->string('trigger_source')->default('manual')->after('session_id');
            }
            if (!Schema::hasColumn('alarm_logs', 'remaining_seconds')) {
                $table->integer('remaining_seconds')->nullable()->after('details');
            }
        });
    }

    public function down()
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            $table->dropColumn(['session_id', 'trigger_source', 'remaining_seconds']);
        });
    }
};