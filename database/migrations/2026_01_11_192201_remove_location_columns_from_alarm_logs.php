<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            if (Schema::hasColumn('alarm_logs', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (Schema::hasColumn('alarm_logs', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('alarm_logs', 'location_text')) {
                $table->dropColumn('location_text');
            }
        });
    }

    public function down()
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('alarm_logs', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('alarm_logs', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('alarm_logs', 'location_text')) {
                $table->text('location_text')->nullable();
            }
        });
    }
};
