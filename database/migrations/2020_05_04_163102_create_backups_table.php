<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBackupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stories', function($table)
        {
            $table->integer('backup_of_story')->after('id');
            $table->string('backup_name')->after('backup_of_story');
            $table->tinyInteger('backup_confirmed')->after('backup_name')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stories', function($table)
        {
            $table->dropColumn('backup_of_story');
            $table->dropColumn('backup_name');
            $table->dropColumn('backup_confirmed');
        });
    }
}
