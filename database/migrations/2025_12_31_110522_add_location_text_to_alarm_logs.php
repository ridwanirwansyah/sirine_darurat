<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            $table->string('location_text')->nullable()->after('longitude');
        });
    }

    public function down()
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            $table->dropColumn('location_text');
        });
    }

};
