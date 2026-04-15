<?php
// database/migrations/2024_01_15_000001_create_alarm_states_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alarm_states', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_on')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('auto_off_at')->nullable();
            $table->integer('auto_off_duration')->default(60); // dalam detik
            $table->string('current_session_id')->nullable(); // untuk tracking session
            $table->boolean('report_created')->default(false);
            $table->string('activated_by')->nullable(); // nama user yang mengaktifkan
            $table->timestamps();
            
            // Index untuk performance
            $table->index('is_on');
            $table->index('auto_off_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('alarm_states');
    }
};