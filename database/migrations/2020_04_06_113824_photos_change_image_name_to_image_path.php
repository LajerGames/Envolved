<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhotosChangeImageNameToImagePath extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('story_photos', function(Blueprint $table) {
            $table->renameColumn('image_name', 'image_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('story_photos', function(Blueprint $table) {
            $table->renameColumn('image_path', 'image_name');
        });
    }
}
