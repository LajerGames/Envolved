<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhotosRemoveSentOnAndAddDaysAgoAndSentTie extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('story_photos', function (Blueprint $table) {
            $table->dropColumn('taken_on');
            $table->tinyInteger('days_ago')->after('image_name');
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
        Schema::table('story_photos', function (Blueprint $table) {
            $table->dateTime('taken_on')->after('image_name');
            $table->dropColumn('days_ago');
            $table->dropColumn('time');
        });
    }
}
