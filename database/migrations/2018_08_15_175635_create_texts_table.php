<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('story_phone_number_texts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('phone_number_id');
            $table->boolean('is_seen')->default(false);
            $table->enum('sender', ['number', 'protagonist']);
            $table->mediumText('text');
            $table->string('filename');
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
        Schema::dropIfExists('story_phone_number_texts');
    }
}
