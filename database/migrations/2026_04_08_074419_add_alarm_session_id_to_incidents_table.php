// database/migrations/xxxx_add_alarm_session_id_to_incidents_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlarmSessionIdToIncidentsTable extends Migration
{
    public function up()
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->string('alarm_session_id')->nullable()->after('images');
            $table->index('alarm_session_id');
        });
    }

    public function down()
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('alarm_session_id');
        });
    }
}