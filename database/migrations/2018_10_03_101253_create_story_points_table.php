<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoryPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('story_story_points', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('story_id');
            $table->integer('story_arch_id');
            $table->integer('number');
            $table->enum('type', [
                'change_variable',
                'wait',
                'condition',
                'change_master_data',
                'text_incomming',
                'text_outgoing',
                'phone_call_incomming_voice',
                'phone_call_outgoing_voice',
                'insert_news_item'
            ]);
            $table->text('instructions_json');
            $table->integer('leads_to');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('story_story_points');
    }
}
