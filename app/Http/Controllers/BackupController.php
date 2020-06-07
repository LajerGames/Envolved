<?php

namespace App\Http\Controllers;

use App\Backup;
use App\Http\Controllers\StoriesController;
use App\Common\Permission;
use App\StoryArch;
use Illuminate\Http\Request;
use App\Common\HandleFiles;
use App\Story;

class BackupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($story_id)
    {
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        // Find all stories that are backups of this story
        $backups = Story::where('backup_of_story', $story_id)->where('backup_confirmed', 1)->orderBy('updated_at', 'DESC')->get();

        // Make array to send along to the view
        $info = [
            'story'     => $story,
            'backups'   => $backups
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
        $storyID = intval($_POST['data']['story_id']);

        $story = Story::find($storyID);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        /**
         * SO!
         * We're about to copy an entire story.
         * We will store the old ID's in arrays (maps) and use them to ensure that everything fits together as it did in the old version
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

        # Variables

            $variablesMap = [];
            $variablesCollection = $this->LoopAndCloneCollection($story->variables, $variablesMap);

        # Story Archs

            $storyArchsMap = [];
            $storyArchCollection = $this->LoopAndCloneCollection($story->storyarchs, $storyArchsMap);

         # Story Points

            $storyPointsMap = [];
            $storyPointCollection = $this->LoopAndCloneCollection($story->storypoints, $storyPointsMap);

         # Copy files to new folder

            $handleFiles = new HandleFiles();
            $handleFiles->copyFilesAndFolders(public_path().'/storage/stories/'.$story->id, public_path().'/storage/stories/'.$newStory->id);

        // END SECTION

        // SECTION CHANGE RELEVANT VALUES

        // Second array key is 0 because everything has story ID -1
        $fieldsStory = ['story_id' => [-1 => $newStory->id]];

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

        # Variables

            $this->LoopCollectionAndUpdateValues($variablesCollection, $fieldsStory);

        # Story Archs

            $fieldsStoryArchs = $fieldsStory;
            $fieldsStoryArchs['start_story_point_id'] = $storyPointsMap;
            $this->LoopCollectionAndUpdateValues($storyArchCollection, $fieldsStoryArchs);

        # Story Archs

            $fieldsStoryPoints = $fieldsStory;
            $fieldsStoryPoints['story_arch_id'] = $storyArchsMap;
            $fieldsStoryPoints['custom'] = [
                'story_points' => [
                    'story_archs_map'   => $storyArchsMap,
                    'story_points_map'  => $storyPointsMap,
                    'characters_map'    => $charactersMap,
                    'variables_map'     => $variablesMap,
                    'phone_numbers_map' => $phoneNumbersMap,
                    'news_items_map'    => $newsMap
                ]
            ];
            $this->LoopCollectionAndUpdateValues($storyPointCollection, $fieldsStoryPoints);

        // END SECTION
    }

    private function LoopCollectionAndUpdateValues($collection, $fields) {

        if(!empty($collection) && is_countable($collection)) {

            // Loop through the collection to find all the models that needs data altered
            foreach($collection as $model) {

                // Loop through the changes that needs to be made and make them
                foreach($fields as $field => $values) {

                    if(!empty($model->{$field})) {
                        $model->{$field} = $values[$model->{$field}];
                    }

                }

                // Do we have anything custom tweak for this model?
                if(isset($fields['custom']) && is_array($fields['custom'])) {

                    // Custom handle this
                    $model = $this->CustomUpdateModel($model, $fields['custom']);

                }

                $model->save();

            }

        }


    }

    private function CustomUpdateModel($model, $custom) {

        if(!empty($custom) || is_countable($custom)) {
            foreach($custom as $type => $maps) {

                // Prepare the fact that there may be other types
                switch($type) {
                    case 'story_points' :

                        $model = $this->CustomUpdateModelStoryPoints($model, $maps);

                        break;
                }

            }
        }

        return $model;

    }

    private function CustomUpdateModelStoryPoints($model, $custom) {

        $model = $this->CustomUpdateModelStoryPointsInstructions($model, $custom);
        $model = $this->CustomUpdateModelStoryPointsLeadsTo($model, $custom);

        return $model;
    }

    private function CustomUpdateModelStoryPointsLeadsTo($model, $custom)
    {
        $leadsTo = json_decode($model->leads_to_json);

        if(is_array($leadsTo)) {

            foreach($leadsTo as $entry => $leads) {

                // Is this a point or an arch
                if(property_exists($leads, 'point')) {
                    $leadsTo[$entry] = $this->GetNewValueFromMap($leadsTo[$entry], 'point', $custom['story_points_map']);
                }
                else {
                    $leadsTo[$entry] = $this->GetNewValueFromMap($leadsTo[$entry], 'arch', $custom['story_archs_map']);
                }

            }
        }

        $model->leads_to_json = (is_countable($leadsTo) && count($leadsTo) > 0) || is_object($leadsTo) ? json_encode($leadsTo) : '';

        return $model;
    }

    private function CustomUpdateModelStoryPointsInstructions($model, $custom) {

        $instructions   = json_decode($model->instructions_json);

        // What kind of story point is this? (we only have the types we need to change)
        switch($model->type) {
            case 'condition' :

                if(is_object($instructions)) {

                    foreach($instructions as $entry => $obj) {

                        $instructions->{$entry} = $this->GetNewValueFromMap($obj, 'variable', $custom['variables_map']);
                        $instructions->{$entry} = $this->GetNewValueFromMap($obj, 'leads_to', $custom['story_points_map']);

                    }

                }

                break;
            case 'change_variable' :

                $instructions = $this->GetNewValueFromMap($instructions, 'variable_id', $custom['variables_map']);

                break;
            case 'redirect' :

                if(isset($instructions->type)) {

                    if($instructions->type == 'story_point') // This is a story point
                        $instructions = $this->GetNewValueFromMap($instructions, 'redirect_id', $custom['story_points_map']);
                    else // This is a story arch
                        $instructions = $this->GetNewValueFromMap($instructions, 'redirect_id', $custom['story_archs_map']);

                }

                break;
            case 'phone_number_change_arch' :

                $instructions = $this->GetNewValueFromMap($instructions, 'phone_number_id', $custom['phone_numbers_map']);
                $instructions = $this->GetNewValueFromMap($instructions, 'call_story_arch', $custom['story_archs_map']);
                $instructions = $this->GetNewValueFromMap($instructions, 'text_story_arch', $custom['story_archs_map']);

                break;
            case 'text_incomming' :

                $instructions = $this->GetNewValueFromMap($instructions, 'from_character_id', $custom['characters_map']);

                break;
            case 'text_outgoing' :

                $instructions = $this->GetNewValueFromMap($instructions, 'to_character_id', $custom['characters_map']);
                $instructions = $this->SwitchPropertyAccordingToStoryPointMap($instructions, $custom['story_points_map']);

                break;
            case 'phone_call_incomming_voice' :

                $instructions = $this->GetNewValueFromMap($instructions, 'from_character_id', $custom['characters_map']);
                $instructions = $this->GetNewValueFromMap($instructions, 'if_user_hang_up_start_arch', $custom['story_archs_map']);
                $instructions = $this->GetNewValueFromMap($instructions, 'if_user_hang_up_start_story_point', $custom['story_points_map']);

                break;
            case 'phone_call_outgoing_voice' :

                $instructions = $this->GetNewValueFromMap($instructions, 'to_character_id', $custom['characters_map']);
                $instructions = $this->GetNewValueFromMap($instructions, 'if_user_hang_up_start_arch', $custom['story_archs_map']);
                $instructions = $this->GetNewValueFromMap($instructions, 'if_user_hang_up_start_story_point', $custom['story_points_map']);
                $instructions = $this->SwitchPropertyAccordingToStoryPointMap($instructions, $custom['story_points_map']);


                break;
            case 'insert_news_item' :

                $instructions = $this->GetNewValueFromMap($instructions, 'news_item', $custom['news_items_map']);

                break;
            case 'start_new_thread' :

                $instructions = $this->GetNewValueFromMap($instructions, 'spawn_new_thread_arch', $custom['story_archs_map']);

                break;
            case 'start_watcher' :

                $instructions = $this->GetNewValueFromMap($instructions, 'arch', $custom['story_archs_map']);

                break;
        }

        $model->instructions_json = (is_countable($instructions) && count($instructions)) || is_object($instructions) > 0 ? json_encode($instructions) : '';

        return $model;

    }

    private function SwitchPropertyAccordingToStoryPointMap($instruction, $storyPointMap) {

        // Loop through to find all leads to (which in this case is object properties)
        if(is_object($instruction)) {

            foreach($instruction as $entry => $obj) {

                if(is_numeric($entry) && is_object($obj)) {

                    $instruction = (array)$instruction;

                    $instruction[$storyPointMap[$entry]] = $obj;
                    unset($instruction[$entry]);

                    $instruction = (object)$instruction;

                }

            }

        }

        return $instruction;

    }

    private function GetNewValueFromMap($obj, $property, $map) {

        // Does the property exist
        if(is_object($obj) && property_exists($obj, $property) && is_numeric($obj->{$property})) {

            if(isset($map[$obj->{$property}]) && is_numeric($map[$obj->{$property}]) && $map[$obj->{$property}] > 0) {
                $obj->{$property} = $map[$obj->{$property}];
            }

        }
        return  $obj;
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
        if(isset($attributes['story_id'])) {
            $attributes['story_id'] = -1;
        }

        $modelClone->setRawAttributes($attributes, true);

        // Set exists to 0 to ensure that a new record is created
        $modelClone->exists = 0;

        return $modelClone;

    }

    public function confirm() {

        $storyID = intval($_POST['data']['story_id']);

        $story = Story::find($storyID);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        // Confirm the latest backup

        // Find the story
        $story = Story::where('user_id', auth()->user()->id)
            ->where('backup_of_story', $storyID)
            ->orderBy('id', 'DESC')
            ->first();

        $story->backup_confirmed = 1;
        $story->save();

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Backup  $Backup
     * @return \Illuminate\Http\Response
     */
    public function destroy($story_id, $id)
    {
        $storiesController = new StoriesController();

        $story = Story::find($id);

        $storiesController->destroy($id);

        return redirect('/stories/'.$story_id.'/backup/')->with('success', $story->title .' deleted');
    }

    public function implement($story_id, $id)
    {
        $storyID = intval($story_id);
        $backupStoryID = intval($id);

        // Check story ownership
        $story = Story::find($storyID);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        // check backup ownership
        $backupStory = Story::find($id);

        if(!Permission::CheckOwnership(auth()->user()->id, $backupStory->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        // Now it's time to pull the ooooool' switch'a'roo!
        // The backup becomes the primary and the primary becomes the backup.

        // Make the current story into a backup
        $story->backup_of_story = $backupStory->id;
        $story->backup_name = $story->title.' (Autogenerated)';
        $story->backup_confirmed = 1;
        $story->save();

        // Now make the backup into the current story
        $backupStory->backup_of_story = 0;
        $backupStory->backup_name = '';
        $backupStory->backup_confirmed = 0;
        $backupStory->save();

        // Last, but not least. Run trough all backups of the current story $story and make sure all backups of that story are now backups of the newly implemented backup
        $otherBackups = Story::where('backup_of_story', $story->id)->where('backup_confirmed', 1)->where('id', '!=', $backupStory->id)->get();

        if(!empty($otherBackups) || is_countable($otherBackups)) {

            foreach($otherBackups as $otherBackup) {

                $otherBackup->backup_of_story = $backupStory->id;
                $otherBackup->save();

            }

        }

        return redirect('/stories/'.$backupStory->id)->with('success', 'Backup implementeret');


        return;
    }
}
