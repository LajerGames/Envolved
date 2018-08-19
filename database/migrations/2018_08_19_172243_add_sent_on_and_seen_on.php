<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSentOnAndSeenOn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('story_phone_number_texts', function($table) {
            $table->dateTime('sent_on')->after('filemime');
            $table->dateTime('seen_on')->after('is_seen');
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
            $table->dropColumn('sent_on');
            $table->dropColumn('seen_on');
        });
    }
}
