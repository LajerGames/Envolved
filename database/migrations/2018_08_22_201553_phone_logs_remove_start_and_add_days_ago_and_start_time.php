<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhoneLogsRemoveStartAndAddDaysAgoAndStartTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('story_phone_logs', function (Blueprint $table) {
            $table->tinyInteger('days_ago')->after('start');
            $table->time('start_time')->after('days_ago');
            $table->dropColumn('start');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('story_phone_logs', function (Blueprint $table) {
            $table->dateTime('start')->after('days_ago');
            $table->dropColumn('days_ago');
            $table->dropColumn('start_time');
            
        });
    }
}
