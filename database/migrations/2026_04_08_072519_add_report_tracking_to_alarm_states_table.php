// database/migrations/xxxx_add_report_tracking_to_alarm_states_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReportTrackingToAlarmStatesTable extends Migration
{
    public function up()
    {
        Schema::table('alarm_states', function (Blueprint $table) {
            $table->string('report_created_for_session')->nullable()->after('auto_off_duration');
            $table->timestamp('report_created_at')->nullable()->after('report_created_for_session');
        });
    }

    public function down()
    {
        Schema::table('alarm_states', function (Blueprint $table) {
            $table->dropColumn(['report_created_for_session', 'report_created_at']);
        });
    }
}