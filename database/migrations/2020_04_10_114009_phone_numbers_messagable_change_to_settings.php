<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhoneNumbersMessagableChangeToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('story_phone_numbers', function($table) {
            $table->text('settings')->after('name');
            $table->dropColumn('messagable');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('story_phone_numbers', function($table) {
            $table->dropcolumn('settings');
            $table->tinyInteger('messagable')->after('name');
        });
    }
}
