<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\Common\Permission;

class ExportController extends Controller
{
    private $newDBPath,
            $newDBName,
            $SQLiteDB;
    /**
     * Display a listing of the resource.
     *
     * @param  int  $story_id
     * @return \Illuminate\Http\Response
     */
    public function exportSQLite($story_id)
    {
        $story = Story::find($story_id);

        if(
            !Permission::CheckOwnership(auth()->user()->id, $story->user_id)
        )
            return false;

        // Save Path info
        $this->newDBPath = public_path().'/storage/story_drafts/'.$story_id.'/';
        $this->newDBName = str_replace('+', '-', urlencode(strtolower($_POST['data']['name'])));

        // Check If story dir exists and create if not
        $this->createSQLiteFolder(public_path().'/storage/story_drafts/'.$story_id);
        $this->createSQLiteFolder(public_path().'/storage/story_drafts/'.$story_id.'/'.$this->newDBName);

        // Find the empty.sqlite and copy it to the story
       if($this->copyEmptySQLiteFile()) {
           
            // Connect to the SQLite file
            $this->SQLiteDB = new \PDO('sqlite:'.$this->newDBPath.'/'.$this->newDBName.'/'.$this->newDBName.'.sqlite');

            $this->SQLiteDB->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Get the story model
            $story = Story::find($story_id);

            # Story
            $this->createTableAndInsertData($story, 'stories', ['user_id', 'created_at', 'updated_at']);

            /*
            $this->SQLiteDB->exec("
                CREATE TABLE IF NOT EXISTS Requests
                (
                    ID INTEGER PRIMARY KEY AUTOINCREMENT, 
                    bla11 VARCHAR( 225 ), 
                    bla21 VARCHAR( 225 )
                )
            ");*/

       }

        return;
    }

    /**
     * Creates directory for story SQLite file if it does not exist
     */
    private function createSQLiteFolder($path) {

        if(!file_exists($path)) {
            mkdir($path);
        }

    }

    /**
     * Copies the empty SQLitefile to the belonging storyfolder
     */
    private function copyEmptySQLiteFile() {

        // Can we find the empty file
        if(file_exists(base_path().'/database/empty.sqlite')) {

            if(!copy(base_path().'/database/empty.sqlite', $this->newDBPath.'/'.$this->newDBName.'/'.$this->newDBName.'.sqlite')) {
                return false;
            } else {
                return true;
            }

        } else {
            return false;
        }

    }

    private function createTableAndInsertData($model, $tableName, $exceptions) {

        // Get all fields
        $fields = $this->getTableFields($tableName, $exceptions);

        // Loop through the relevant fields and save the columns in the table
        $columns = '';
        foreach($fields as $fieldInfo) {
            $columns .= $fieldInfo->Field.' '.
                        $this->translateSQLiteColumnType($fieldInfo->Type)
                        .$this->translateSQLiteColumnKey($fieldInfo->Key)
                        .$this->translateSQLiteColumnExtra($fieldInfo->Extra).',';
        }

        $columns = rtrim($columns, ',');

        $tableName = $this->createSQLiteTableName($tableName);
        $this->SQLiteDB->exec('
            CREATE TABLE IF NOT EXISTS '.$tableName.'
            (
                '.$columns.'
            )
        ');

        $attributes = array_keys($model->getAttributes());

        print_r($attributes);

        foreach($model->all() as $key => $value) {
            print_r($value);
        }

    }

    /**
     * Returns an array with all relevant fields for the table we're currently going through
     */
    private function getTableFields($tableName, $exceptions) {

        $fields = \DB::select('SHOW FIELDS FROM '. $tableName);

        // Remove fields in exceptions
        foreach($fields as $key => $fieldValues) {

            if(in_array($fieldValues->Field, $exceptions)) {
                unset($fields[$key]);
            }

        }

        return $fields;
    }

    private function createSQLiteTable() {

    }

    /**
     * We'll need to rename the tables a bit to fit the SQLite database
     */
    private function createSQLiteTableName($tableName) {
        $SQLiteTableName = '';
        if($tableName == 'stories') {
            $SQLiteTableName = 'properties';
        } else {
            $SQLiteTableName = str_replace('story_', '', $tableName);
        }

        return $SQLiteTableName;
    }

    /**
     * SECTION: Translates into correct SQL language
     */
    private function translateSQLiteColumnType($type) {
        return strtoupper($type);
    }
    private function translateSQLiteColumnKey($key) {
        $keyTranslation = '';
        if($key == 'PRI') {
            $keyTranslation = ' PRIMARY KEY ';
        }
        return $keyTranslation;
    }
    private function translateSQLiteColumnExtra($extra) {
        $extraTranslation = '';
        if($extra == 'auto_increment') {
            $extraTranslation = ' AUTOINCREMENT ';
        }
        return $extraTranslation;
    }
    /** END SECTION */
}
