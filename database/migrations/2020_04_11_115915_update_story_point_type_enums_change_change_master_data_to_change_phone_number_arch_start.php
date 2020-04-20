<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateStoryPointTypeEnumsChangeChangeMasterDataToChangePhoneNumberArchStart extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement("ALTER TABLE story_story_points CHANGE COLUMN type type ENUM(
            'change_variable',
            'wait',
            'condition',
            'redirect',
            'phone_number_change_arch',
            'text_incomming',
            'text_outgoing',
            'phone_call_incomming_voice',
            'phone_call_outgoing_voice',
            'phone_call_hang_up',
            'insert_news_item',
            'start_new_thread',
            'end_thread',
            'end_game'
        ) NOT NULL DEFAULT 'wait'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE story_story_points CHANGE COLUMN type type ENUM(
            'change_variable',
            'wait',
            'condition',
            'redirect',
            'change_master_data',
            'text_incomming',
            'text_outgoing',
            'phone_call_incomming_voice',
            'phone_call_outgoing_voice',
            'phone_call_hang_up',
            'insert_news_item',
            'start_new_thread',
            'end_thread',
            'end_game'
        ) NOT NULL DEFAULT 'wait'");
    }
}
