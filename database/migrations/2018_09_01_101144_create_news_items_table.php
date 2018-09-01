<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('story_module_news', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('story_id');
            $table->string('headline');
            $table->string('image');
            $table->string('teaser_text');
            $table->text('article_json');
            $table->tinyInteger('days_ago');
            $table->time('time');
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
        Schema::dropIfExists('story_module_news');
    }
}
