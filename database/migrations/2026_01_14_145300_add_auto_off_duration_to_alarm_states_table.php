<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alarm_states', function (Blueprint $table) {
            $table->integer('auto_off_duration')->default(60)->comment('Durasi auto-off dalam detik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alarm_states', function (Blueprint $table) {
            $table->dropColumn('auto_off_duration');
        });
    }
};
