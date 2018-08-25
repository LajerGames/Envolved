<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhoneLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('story_phone_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('story_id');
            $table->integer('phone_number_id');
            $table->dateTime('start');
            $table->tinyInteger('minutes');
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
        Schema::dropIfExists('story_phone_logs');
    }
}
