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

            // Create each and every relevant table
            foreach($this->getTablesToSaveArray() as $tableName => $instructions) {

                // Choose the right model
                $model = empty($instructions['model_name']) ? $story : $story->{$instructions['model_name']};

                // Create and insert
                $this->createTableAndInsertData($model, $tableName, $instructions['cell_exceptions']);
            }
return;

            # Story
            //$this->createTableAndInsertData($story, 'stories', ['user_id', 'created_at', 'updated_at']);
            //$this->createTableAndInsertData($story->characters, 'story_characters', ['story_id', 'created_at', 'updated_at']);

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

        // Create the table
        $SQLiteTableName = $this->createSQLiteTableName($tableName);
        $this->createSQLiteTable($fields, $SQLiteTableName);

        // Insert the data from the model into the newly created SQLite table
        $this->insertSQLiteData($model, $fields, $SQLiteTableName);

        return;
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

    /**
     * Insert the actual table
     */
    private function createSQLiteTable($fields, $tableName) {

        // Loop through the relevant fields and save the columns in the table
        $columns = '';
        foreach($fields as $fieldInfo) {
            $columns .= $fieldInfo->Field.' '.
                        $this->translateSQLiteColumnType($fieldInfo->Type)
                        .$this->translateSQLiteColumnKey($fieldInfo->Key)
                        .$this->translateSQLiteColumnExtra($fieldInfo->Extra).',';
        }

        $columns = rtrim($columns, ',');

        $this->SQLiteDB->exec('
            CREATE TABLE IF NOT EXISTS '.$tableName.'
            (
                '.$columns.'
            )
        ');

        return;
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
     * Inserts sqlite data from a model into a table
     */
    private function insertSQLiteData($model, $fields, $SQLiteTableName) {

        $values = ''; // Dis won gun contain da values for da insert :P (tired, don't judge me!)
        $insertFields = ''; // Columns for the INSERT

        // If more than one record is present this will be a collection, let's treat it right
        if(is_a($model, 'Illuminate\Database\Eloquent\Collection')) {
            $isFirstLoop = true;
            foreach($model as $value) {

                // Use the fields we've isolated to find out what to save
                $loopValue = $this->loopThroughFieldsToInsertData($fields, $value, $insertFields, $values);                
                $values = $loopValue['values'];
                if($isFirstLoop) {
                    $insertFields = $loopValue['insertFields'];
                    $isFirstLoop = false;
                }
    
            }
            

        } else {

            // Use the fields we've isolated to find out what to save
            $loopValue = $this->loopThroughFieldsToInsertData($fields, $model, $insertFields, $values);                
            $values = $loopValue['values'];
            $insertFields = $loopValue['insertFields'];
            
        }

        // Create the insert query
        $query = 'INSERT INTO '.$SQLiteTableName.' ('.rtrim($insertFields, ',').') VALUES '.rtrim($values, ',');

        /*
        echo $query;
        return;
        */

        $stmt = $this->SQLiteDB->prepare($query);
        $stmt->execute();
    }

    /** 
     * Loops through a model based on the fields we've isolated earlier (the ones that are not excluded)
     * Based in this loop it will create the COLUMNS we're inserting into and the VALUES we're insering
     * It's not really that difficult even though I might have been able to name shit better.. sorry... This is relatively simple :)
     */
    private function loopThroughFieldsToInsertData($fields, $model, $insertFields, $values) {
        $values .= '(';
        foreach($fields as $key => $fieldValues) {
    
            $insertFields .= $fieldValues->Field.','; 
            $values .= '\''.str_replace("'", "''", $model->{$fieldValues->Field}).'\',';

        }

        return [
            'insertFields' => $insertFields,
            'values' => rtrim($values, ',').'),'
        ];
    }

    /**
     * Returns the array of tables to loop through. Each key is a table name, and value contains instructions
     */
    private function getTablesToSaveArray() {

        /*
        return [
            'story_phone_number_texts' => $this->makeTablesToSaveArrayValueArray('texts', ['story_id', 'created_at', 'updated_at']),
        ];
        */

        return [
            'stories' => $this->makeTablesToSaveArrayValueArray('', ['user_id', 'created_at', 'updated_at']),
            'story_characters' => $this->makeTablesToSaveArrayValueArray('characters', ['story_id', 'created_at', 'updated_at']),
            'story_module_news' => $this->makeTablesToSaveArrayValueArray('news', ['story_id', 'created_at', 'updated_at']),
            'story_phone_logs' => $this->makeTablesToSaveArrayValueArray('phonelogs', ['story_id', 'created_at', 'updated_at']),
            'story_phone_numbers' => $this->makeTablesToSaveArrayValueArray('phonenumber', ['story_id', 'name', 'created_at', 'updated_at']),
            'story_phone_number_texts' => $this->makeTablesToSaveArrayValueArray('texts', ['story_id', 'created_at', 'updated_at']),
            'story_photos' => $this->makeTablesToSaveArrayValueArray('photos', ['story_id', 'created_at', 'updated_at']),
            'story_variables' => $this->makeTablesToSaveArrayValueArray('variables', ['story_id', 'created_at', 'updated_at']),
        ];
    }
    private function makeTablesToSaveArrayValueArray($modelName, $cellExceptions) {
        return [
            'cell_exceptions' => $cellExceptions,
            'model_name' => $modelName
        ];
    }

    /**
     * SECTION: Translates into correct SQL language
     */
    private function translateSQLiteColumnType($type) {
        if(strpos($type, 'int(') !== false) {
            $type = 'INTEGER';
        } elseif(strpos($type, 'enum(') !== false) {
            $type = 'TEXT';
        } else {
            $type = strtoupper($type);
        }
        return $type;
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
