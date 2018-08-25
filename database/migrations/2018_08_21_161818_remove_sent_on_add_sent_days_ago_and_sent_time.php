<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveSentOnAddSentDaysAgoAndSentTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('story_phone_number_texts', function($table) {
            $table->dropColumn('sent_on');
            $table->dropColumn('seen_on');
            $table->tinyInteger('days_ago')->after('filemime');
            $table->time('time')->after('days_ago');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('story_phone_number_texts', function($table) {
            $table->dateTime('sent_on')->after('filemime');
            $table->dateTime('seen_on')->after('is_seen');
            $table->dropColumn('days_ago');
            $table->dropColumn('time');
        });
    }
}
