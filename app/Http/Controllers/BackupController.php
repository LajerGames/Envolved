<?php

namespace App\Http\Controllers;

use App\Backup;
use App\Common\Permission;
use App\StoryArch;
use Illuminate\Http\Request;
use App\Common\HandleFiles;
use App\Story;
use App\Character;
use App\NewsItem;
use App\PhoneLog;
use App\PhoneNumber;
use App\Text;
use App\Photo;

class BackupController extends Controller
{
    private $storyID = 0;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Make array to send along to the view
        $info = [
            'story' => 'sad',
            'phone_logs' => 'sad',
            'phone_numbers_select' => 'sad',
            'time' => 'sad',
            'days_ago' => 'sad'
        ];

        return view('stories.Backups.index')->with('info', $info);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $name = $_POST['data']['name'];
        $storyID = $_POST['data']['story_id'];

        $story = Story::find($storyID);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        /**
         * SO!
         * We're about to copy an entire story.
         * We will store the old ID's in arrays and use them to ensure that everything fits together as it did in the old version
         */

        // SECTION: Do all of the copying

        # Story

            $newStory = $this->CloneModel($story);

            // Tell the system that this is backup of the particular story ID
            $newStory->backup_of_story = $storyID;
            $newStory->backup_name = $name;
            $newStory->save();

        # Characters
        
            $charactersMap = [];
            $characterCollection = $this->LoopAndCloneCollection($story->characters, $charactersMap);

        # News
        
            $newsMap = [];
            $newsCollection = $this->LoopAndCloneCollection($story->news, $newsMap);

        # Phone Logs
        
            $phoneLogsMap = [];
            $phoneLogsCollection = $this->LoopAndCloneCollection($story->phonelogs, $phoneLogsMap);

        # Phone Numbers
        
            $phoneNumbersMap = [];
            $phoneNumberCollection = $this->LoopAndCloneCollection($story->phonenumber, $phoneNumbersMap);

        # Phone Numbers Texts
        
            $phoneNumbersTextsMap = [];
            $phoneNumbersTextCollection = $this->LoopAndCloneCollection($story->texts, $phoneNumbersTextsMap);

        # Photos
        
            $photosMap = [];
            $photoCollection = $this->LoopAndCloneCollection($story->photos, $photosMap);

        # Settings
        
            $settingsMap = [];
            $settingCollection = $this->LoopAndCloneCollection($story->settings, $settingsMap);

        # Story Archs

            $storyArchsMap = [];
            $storyArchCollection = $this->LoopAndCloneCollection($story->storyarchs, $storyArchsMap);

         # Story Points

            $storyPointsMap = [];
            $storyPointCollection = $this->LoopAndCloneCollection($story->storypoints, $storyPointsMap);

        # Variables

            // TODO

         # Copy files to new folder

            //$handleFiles = new HandleFiles();
            //$handleFiles->xcopy(public_path().'/storage/stories/'.$story->id, public_path().'/storage/stories/'.$newStory->id, '0777');

        // END SECTION

        // SECTION CHANGE RELEVANT VALUES

        $fieldsStory = ['story_id' => [$storyID => $newStory->id]];

        # Characters

            $this->LoopCollectionAndUpdateValues($characterCollection, $fieldsStory);

        # News

            $fieldsNews = $fieldsStory;
            $fieldsNews['character_id'] = $charactersMap;
            $this->LoopCollectionAndUpdateValues($newsCollection, $fieldsNews);

        # Phone Logs

            $fieldsPhoneLogs = $fieldsStory;
            $fieldsPhoneLogs['phone_number_id'] = $phoneNumbersMap;
            $this->LoopCollectionAndUpdateValues($phoneLogsCollection, $fieldsPhoneLogs);

        # Phone Numbers

            $fieldsPhoneNumbers = $fieldsStory;
            $fieldsPhoneNumbers['character_id'] = $charactersMap;
            $this->LoopCollectionAndUpdateValues($phoneNumberCollection, $fieldsPhoneNumbers);

        # Phone Numbers Text

            $fieldsPhoneNumberTexts = $fieldsStory;
            $fieldsPhoneNumberTexts['phone_number_id'] = $phoneNumbersMap;
            $this->LoopCollectionAndUpdateValues($phoneNumbersTextCollection, $fieldsPhoneNumberTexts);

        # Photos

            $this->LoopCollectionAndUpdateValues($photoCollection, $fieldsStory);

        # Settings

            $this->LoopCollectionAndUpdateValues($settingCollection, $fieldsStory);

        # Story Archs

            $fieldsStoryArchs = $fieldsStory;
            $fieldsStoryArchs['start_story_point_id'] = $storyPointsMap;
            $this->LoopCollectionAndUpdateValues($storyArchCollection, $fieldsStoryArchs);

        # Story Archs

            $fieldsStoryPoints = $fieldsStory;
            $fieldsStoryPoints['story_arch_id'] = $storyArchsMap;
            $fieldsStoryPoints['custom.story_points'] = [
                'story_archs_map'   => $storyArchsMap,
                'story_points_map'  => $storyPointsMap,
                'characters_map'    => $charactersMap
            ];
            $this->LoopCollectionAndUpdateValues($storyPointCollection, $fieldsStoryPoints);

        // END SECTION
    }

    private function LoopCollectionAndUpdateValues($collection, $fields) {

        // Loop through the collection to find all the models that needs data altered
        foreach($collection as $model) {

            // Loop through the changes that needs to be made and make them
            foreach($fields as $field => $values) {

                if(!empty($model->{$field})) {
                    $model->{$field} = $values[$model->{$field}];
                }

            }

            $model->save();

        }

    }

    /**
     * @param $object Object may be a collection or a model
     * @param $arrayMap
     * @return array
     */
    private function LoopAndCloneCollection($object, &$arrayMap) {

        // Create var to store model collection
        $modelCollection = null;

        // Do we have an object
        if($object) {

            // Make modelCollection into an array
            $modelCollection = [];


            if($object instanceof \Illuminate\Database\Eloquent\Model) {

                // If object is a Model, then we'll behave accordingly
                $this->CloneAndHandleModel($object, $modelCollection, $arrayMap);

            } else {

                // Object is a collection - loop through it

                foreach($object as $model) {

                    $this->CloneAndHandleModel($model, $modelCollection, $arrayMap);

                }
            }
        }

        return $modelCollection;

    }

    private function CloneAndHandleModel($model, &$modelCollection, &$arrayMap) {

        if($model) {

            $modelClone = $this->CloneModel($model);

            // Save the new model
            $modelClone->save();

            // Save the new model to the collection
            $modelCollection[] = $modelClone;

            // Ensure that we have mapped the old and new ids
            $arrayMap[$model->id] = $modelClone->id;

        }
    }

    private function CloneModel($model) {
        //print_r($model);
        $modelClone = clone $model;

        $attributes = $modelClone->getAttributes();
        unset($attributes['id']);
        $modelClone->setRawAttributes($attributes, true);

        // Set exists to 0 to ensure that a new record is created
        $modelClone->exists = 0;

        return $modelClone;

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Backup  $Backup
     * @return \Illuminate\Http\Response
     */
    public function destroy(Backup $Backup)
    {
        //
    }
}
