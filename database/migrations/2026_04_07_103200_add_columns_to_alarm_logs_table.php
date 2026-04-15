<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            $table->string('target_type')->nullable()->after('action'); // User, Incident, Alarm
            $table->unsignedBigInteger('target_id')->nullable()->after('target_type'); // ID target
            $table->string('user_name')->nullable()->after('user_id'); // backup nama user
            $table->text('description')->nullable()->after('details');
            $table->json('old_data')->nullable()->after('description');
            $table->json('new_data')->nullable()->after('old_data');
            $table->string('user_agent')->nullable()->after('ip_address');
            
            // Index untuk pencarian
            $table->index(['action', 'created_at']);
            $table->index('target_type');
            $table->index('event_time');
        });
    }

    public function down()
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            $table->dropColumn([
                'target_type', 'target_id', 'user_name', 
                'description', 'old_data', 'new_data', 'user_agent'
            ]);
        });
    }
};