<?php

namespace App\Http\Controllers;

use App\Rules\ValidFile;
use http\Message;
use Illuminate\Http\Request;
use App\Story;
use App\StoryArch;
use App\StoryPoint;
use App\Common\Permission;
use App\Common\HandleSettings;
use App\Common\HandleFiles;
use App\Common\GetNewestValues;

class StoryPointsController extends Controller
{
    /**
     * Insert new Story point
     *
     * @return \Illuminate\Http\Response
     */
    public function InsertStoryPoint(){
        $story_id = intval($_POST['data']['story_id']);
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');
        
        // Initialize POST data
        $storyArchID            = intval($_POST['data']['story_arch_id']);
        $storyArch              = $story->storyarchs->find($storyArchID);
        $storyPoints            = $storyArch->storypoints;

        // We may get a parent number, which means that we have to look in this storyarch and find the given number
        // Otherwise we get a parent ID, that means we will just need to use that to find the parent StoryPoint
        if(intval($_POST['data']['parent_id']) > 0) {

            // We have a parent ID, just find the story_point
            $parentStoryPoint   = $storyPoints->find(intval($_POST['data']['parent_id']));

        } else {

            // No Parent ID, just a parent number. Go through the given story Arch to get the required StoryPoint
            $parentStoryPointNumber = intval($_POST['data']['parent_number']);
            $parentStoryPoint       = $storyPoints->where('number', $parentStoryPointNumber)->first();

        }
        
        $type                   = $_POST['data']['type'];

        // Now to catch a potential error. If we insert a new StoryPoint with no parentStoryPoint, then please check if we already have a starting point.
        // If not, then proceed. If we do, then return an error
        if(!$parentStoryPoint instanceof StoryPoint && $storyArch->start_story_point_id > 0) {

            // Return an error
            echo json_encode(['error' => 'no_parent_id']);

            return;
        }

        // So if we don't have a number, let's make it the newest one.
        $highestStoryPointNumber = GetNewestValues::Build($storyPoints, ['number'], 'number');
        $number = intval($highestStoryPointNumber['number'])+1;
        
        // So, we're inserting a new story point not updating one
        $storyPoint = new StoryPoint;
        $this->SavePost($storyPoint, $number);

        // Now check if parentStoryPointNumber is == 0, if it is, then it means that we must make this the new start story point of this arch
        if(!$parentStoryPoint instanceof StoryPoint) {

            // Get the story arches controller
            $storyArchesController = new StoryArchesController();

            // Update the Story Point Start ID
            $storyArchesController->changeStoryPointStartID($story_id, $storyArchID, $storyPoint->id);

            $parentStoryPointID = 0;

        } else  {

            $this->HandleReference($parentStoryPoint, ['point' => $storyPoint->id]);

            $parentStoryPointID = $parentStoryPoint->id;

        }

        echo json_encode(['story_point_id' => $storyPoint->id, 'parent_story_point_id' => $parentStoryPointID]);

        return;
    }

    /**
     * Adds a story point ID to the references in instructions_json in another story point
     
    private function CreateReference(Story $story, $storyPoint, $parentStoryPoint) {

        

    }*/

    /**
     * Adds or removes a ref from a story point
     */
    private function HandleReference(StoryPoint $storyPoint, $ref, $action = 'add') {

        $leadsTo = [];
        if(!empty($storyPoint->leads_to_json)) {
            $leadsTo = (array)json_decode($storyPoint->leads_to_json);
        }

        switch($action) {
            case 'add' :
                // Let's add the $ref to the leads_to_json in the instructions_json
                $leadsTo[] = $ref;
                break;
            case 'replace' :

                // Find out what type we're bringing along here
                foreach($ref as $removeType => $value) {
                    break;
                }

                // Let's replace everything in the leads_to_json with whatever we recieved - only if we have a value
                if($value > 0) {
                    $leadsTo = [$ref];
                }
                break;
            case 'replace_type' :

                // Find out what type we're bringing along here
                foreach($ref as $removeType => $value) {
                    break;
                }

                // Only do anything if we get a value
                if($value > 0) {

                    // Loop through the leadsTos and remove all of the type $removeType
                    foreach($leadsTo as $entryNo => $object) {
                        if(property_exists($object, $removeType)) {
                            unset($leadsTo[$entryNo]);
                        }
                    }

                    // The array may now be an accosiative array - which bugs me, so we'll now run through it and give it new keys
                    $newLeadsToArray = [];
                    foreach($leadsTo as $object) {
                        $newLeadsToArray[] = $object;
                    }

                    // Set array back to leads to
                    $leadsTo = $newLeadsToArray;

                    // Add te new reference
                    $leadsTo[] = $ref;
                }

                break;
        }

        $storyPoint->leads_to_json = json_encode($leadsTo);

        $storyPoint->save();

        return;
    }

    /**
     * Handle reference called from AJAX
     */
    public function HandleReferenceAjax() {

       $data = $_POST['data'];

       $storyPointID = intval($data['story_point_id']);

       if($storyPointID > 0) {
            $storyPoint = StoryPoint::find($storyPointID);

            // Reference looks different if we'rereferencing an arch
            $reference = $data['type'] == 'story_arch' ? ['arch' => intval($data['ref'])] : ['point' => intval($data['ref'])];
            $this->HandleReference($storyPoint, $reference, $data['action']);
       }

       return;
    }

    /**
     * Returns HTML for all leads to for a particular story point
     */
    public function UpdateStoryPointLeadsTo() {
        $data = $_POST['data'];
        
        $story_id = intval($data['story_id']);
        $storyPointID = intval($data['story_point_id']);

        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        // Get the right story point
        $storyPoint = $story->StoryPoints->find($storyPointID);

        return $this->GetStoryPointLeadsToMarkup($storyPoint);
    }

    /**
     * Returns HTML for all leads to for a particular story point
     */
    private function GetStoryPointLeadsToMarkup($storyPoint, $jsonEncode = true) {
        $story = $storyPoint->story;        

        $return = '';
        if(isset($storyPoint->leads_to_json) && !empty($storyPoint->leads_to_json)) {
            $leadsToJson = json_decode($storyPoint->leads_to_json);
            
            foreach($leadsToJson as $leadsToID) {

                if(is_object($leadsToID)) {
                    // leadsToID is an object and it might lead to either a storyarch of a storypoint - find out which
                    if(property_exists($leadsToID, 'point')) {
                        // This leads to a storypoint
    
                        // Get this particular story point
                        $storyPoint = $story->StoryPoints->find(intval($leadsToID->point));
    
                        // Get the color
                        $color = config('constants.story_points')[$storyPoint->type][1];
    
                        $return .= '<a class="id-number-circle story-point-leads-to-reference" href data-story-point-no="'.$storyPoint->number.'" style="background-color:'.$color.'">'.$storyPoint->number.'</a>';
                    }
                    else {
                        // This leads to a storyarch
                        $return .= '<a href="/stories/'.($storyPoint->story->id).'/builder/arch/'.$leadsToID->arch.'" class="id-number-circle" style="background-color:#0D0">A</a>';
                    }
    
                }

            }

            return $jsonEncode ? json_encode($return) : $return;
        }
    }

    /**
     * Save or update a story point
     *
     * @return \Illuminate\Http\Response
     */
    public function GetStoryPointAndRenderContainer() {
        $story_id = intval($_POST['data']['story_id']);
        $story_point_id = intval($_POST['data']['story_point_id']);
        $storyPoint = $this->GetStoryPoint(
            $story_id,
            $story_point_id
        );

        if($storyPoint instanceof StoryPoint) {
            echo json_encode([
                'story_point_id' => $story_point_id,
                'story_point_html' =>  trim(preg_replace('/\s+/', ' ', $this->RenderStoryPointContainer($storyPoint, $story_id))) // Preg replace is to remove newlines
            ]);
        } else {
            echo json_encode('error');
        }
        return;
    }

    /**
     * Save Story Point Form
     *
     * @return \Illuminate\Http\Response
     */
    public function SaveStoryPointForm(Request $request)
    {
        // Initialize variables
        $data = $_POST;
        $story_id = intval($data['story_id']);
        $story_point_id = intval($data['story_point_id']);

        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');
        /*
        $data = [];
        parse_str($_POST['data']['data'], $data);
        */
        $name = $data['name'];

        // Get the story point
        $storyPoint = $this->GetStoryPoint($story_id, $story_point_id);

        // Did we get a file?
        if($request->file('file') && $request->hasFile('file')) {

            // TODO: HandleFiles needs to be able to create folders if they dont exist

            // Delete old and upload new image
            $imageInfo = HandleFiles::DeleteThenUpload(
                $request,
                $storyPoint,
                'file',
                'file',
                'public/stories/'.$story_id.'/storypoints/'.$story_point_id.'/',
                new ValidFile(false, false, true)
            );

            $fileName = isset($imageInfo['filename']) ? "{$imageInfo['filename']}" : '';

            $storyPoint->file = $fileName;

            $data['json']['file'] = $fileName;
            $data['json']['mimetype'] = isset($imageInfo['mimetype']) ? "{$imageInfo['mimetype']}" : '';
        }

        $json = isset($data['json']) ? json_encode($data['json']) : '';


        // Update the story point json instructions and name
        
        $storyPoint->name = "{$name}";
        $storyPoint->instructions_json = "{$json}";

        $storyPoint->save();

        echo json_encode($name);

        return;
    }

    /**
     * Render story point type form
     *
     * @return \Illuminate\Http\Response
     */
    public function RenderStoryPointTypeForm()
    {
        $storyPointID = intval($_POST['data']['story_point_id']);
        $storyID = intval($_POST['data']['story_id']);
        $storyPoint = $this->GetStoryPoint($storyID, $storyPointID);

        echo json_encode([
            'story_point_id' => $storyPointID,
            'html' =>  trim(preg_replace('/\s+/', ' ', $this->RenderStoryPointForm($storyPoint))) // Preg replace is to remove newlines
        ]);

        return;
    }

    private function GetStoryPoint($story_id, $storyPointID) {

        $story = Story::find($story_id);

        if(
            !Permission::CheckOwnership(auth()->user()->id, $story->user_id)
        )
            return redirect('/stories')->with('error', 'Access denied');

        return $story->storypoints->find($storyPointID);

    }

    /**
     * Renders a story point container.
     */
    public function RenderStoryPointContainer(StoryPoint $storyPoint) {
        $color = config('constants.story_points')[$storyPoint->type][1];

        // Is this the start story point of this story arch
        $startStoryPoint = $storyPoint->storyArch->start_story_point_id == $storyPoint->id 
            ? '<span class="glyphicon glyphicon-fire"></span> ' 
            : '';

        return '
        <div class="story-point-container" data-story-point-no="'.$storyPoint->number.'" data-story-point-id="'.$storyPoint->id.'" data-story-point-type="'.$storyPoint->type.'">
            <div class="story-point-shadow-container">
                <span class="id-number-circle id-number-pos-top" style="background-color:'.$color.'">'.$storyPoint->number.'</span>
                <div class="story-point-container-top" style="background-color:'.$color.'">
                    <div class="story-point-container-top-opacity-icon-container hastip" data-moretext="Shortcut: <b>ctrl + shift + h</b>"><span class="glyphicon glyphicon-eye-open"></span></div>
                    '.$startStoryPoint.'<u>'.ucfirst(str_replace('_', ' ', $storyPoint->type)).'</u>: <span class="story-point-container-top-name">'.$storyPoint->name.'</span>
                </div>
                <div class="story-point-container-middle-and-bottom">
                    <div class="story-point-container-middle" style="border-left:1px solid '.$color.';border-right:1px solid '.$color.';">
                        <div class="story-point-form-container"></div>
                    </div>
                    <div class="story-point-container-bottom" style="background-color:'.$color.'">
                        <div class="story-pointleads-to-container">'.$this->GetStoryPointLeadsToMarkup($storyPoint, false).'</div>
                    </div>
                </div>
            </div>
        </div>
        ';
    }

    /**
     * Renders the form within a storypoint container
     */
    public function RenderStoryPointForm(StoryPoint $storyPoint) {

        $generatedID = uniqid();

        // Some story points has special functions let's render those here.
        $specialElement = '';
        switch($storyPoint->type) {
            case 'redirect' :
                $specialElement = '<input type="hidden" class="run-js-on-save" data-js-function="addLeadsToToStoryPoint" data-type="" data-id="" value="" />';
                break;
            case 'start_new_thread' :
                $specialElement = '<input type="hidden" class="run-js-on-save" data-js-function="addLeadsToToStoryPoint" data-type="story_arch" data-id="" data-action="replace_type" value="" />';
                break;
            case 'phone_call_incomming_voice' :
                $specialElement = '<input type="hidden" class="run-js-on-save" data-js-function="refreshStoryPoint" data-type="" data-id="" data-action="" value="" />';
                break;
        }

        // Get addable storyPoints
        $addableStoryPoints = $this->GetListOfAddableStoryPoints($storyPoint);
        if($addableStoryPoints)
        {
            // Did we get an array in return? If we did, then make sure that we only enable addition of the ones that we allow
            /*if(is_array($addableStoryPoints)) {
                // Todo: enforce list
            }*/
            $addStoryPointButton = '<a href="javascript:void(0);" class="btn btn-default hastip add-story-point-to-this" data-moretext="Shortcut: <b>ctrl + shift + a</b>">Add story-point</a>';
        }
        else
        {
            $addStoryPointButton = '';
        }


        return '
        <form name="story_point" data-generated-id="'.$generatedID.'" enctype="multipart/form-data">
            <input type="hidden" name="story_id" value="'.$storyPoint->story->id.'" />
            <input type="hidden" name="story_point_id" value="'.$storyPoint->id.'" />
            <div class="form-group">
                <label for="'.$generatedID.'_name">Name</label><br />
                <input type="text" id="'.$generatedID.'_name" name="name" value="'.$storyPoint->name.'" placeholder="Name" class="form-control" />
            </div>
            <div class="story-point-form-specialized-input">'.$this->RenderStoryPointFormTypeInputs($storyPoint, $generatedID).'</div>
            '.$specialElement.$addStoryPointButton.'
            <a href="javascript:void(0);" class="btn btn-primary pull-right hastip update-story-point" data-moretext="Shortcut: <b>ctrl + shift + u</b>">Update</a>
            <div class="clear-both"></div>
        </form>
        ';
    }

    /**
     * there are several rules that decides which (if any) new storypoints can be added to this - ask for a list of story points that can be added to this (false if none true if all)
     * Returns: []]
     */
    private function GetListOfAddableStoryPoints(StoryPoint $storyPoint) {

        // So, type of story point is a big deciding factor in which type of storypoint we can add
        $addStoryPoints = true;
        switch($storyPoint->type) {
            case 'redirect' : // You can never add storypoints to redirect
            case 'end_thread' : // You can never add storypoints to end_thread
                $addStoryPoints = false;
                break;
            // Now for a list of types that can only have 1 story point, it may also lead to a story arch, but that doesn't count
            case 'start_new_thread' :

                $leadsTo = json_decode($storyPoint->leads_to_json);

                // Loop through the leads tos, and see if we can find merely one "point" - if we can, then block this type from adding anymore story points
                if(is_object($leadsTo))
                {
                    foreach($leadsTo as $object) {
                        if(property_exists($object, 'point')) {

                            $addStoryPoints = false;

                            break; // No need to loop any further
                        }
                    }
                }

                break;
            // Now for a list of types that can only have 1 child - if it already has that, then block the addition of more
            case 'change_variable' :
            case 'wait' :
            case 'text_incomming' : // incomming texts immidiately leads to a new story-point - but it can only lead to one
            case 'phone_call_incomming_voice' : // incomming voice immidiately leads to a new story-point - but it can only lead to one
            case 'insert_news_item' :
            case 'start_new_thread' :

                // If we wind up in here, that means that we can only add one story-point-child to this story point.
                // If that is already done - then block for the addition of more

                if(!empty($storyPoint->leads_to_json)) {

                    $addStoryPoints = false;

                }

                break;
        }

        // Only proceed if we're even allowed to add more story-points
        if($addStoryPoints) {

            // If this already leads to a reply (either text or voice) - then we can only add more of those

            // Does this already lead somewhere? If yes, then go through them.
            if(!empty($storyPoint->leads_to_json)) {

                // Yes, story point leads somewhere. Check what type it leads to? (Only neccesary to check the first one)
                $leadsTos = json_decode($storyPoint->leads_to_json);
                foreach($leadsTos as $leadsTo) {

                    // This is only relevant if it leads to a point, not it it leads to an arch
                    if(is_object($leadsTo) && property_exists($leadsTo, 'point')) {

                        // This leads to a story point - what storypoint is it
                        $leadsToStoryPoint = $storyPoint->story->storypoints->find($leadsTo->point);

                        switch($leadsToStoryPoint->type) {
                            case 'text_outgoing' :
                            case 'phone_call_outgoing_voice' :

                                // If we've added one of this type, then we can only add this type for the future - anything else wont make sense.
                                $addStoryPoints = [$leadsToStoryPoint->type];

                                break;
                        }

                    }


                    break;
                }

            }

        }

        return $addStoryPoints;
    }

    

    // section: Form Type Inputs
    private function RenderStoryPointFormTypeInputs(StoryPoint $storyPoint, $generatedID) {
        $return = '';
        $values = json_decode($storyPoint->instructions_json);

        switch($storyPoint->type) {
            case 'change_variable' :
                $return = $this->RenderStoryPointFormTypeInputsChangeVariable($values, $generatedID, $storyPoint);
                break;
            case 'wait' :
                $return = $this->RenderStoryPointFormTypeInputsWait($values, $generatedID);
                break;
            case 'condition' :
                $return = $this->RenderStoryPointFormTypeVariableConditions($values, $generatedID, $storyPoint);
                break;
            case 'redirect' :
                $return = $this->RenderStoryPointFormTypeRedirect($values, $generatedID, $storyPoint);
                break;
            case 'text_incomming' :
                $return = $this->RenderStoryPointFormTextIncomming($values, $generatedID, $storyPoint);
                break;
            case 'text_outgoing' :
                $return = $this->RenderStoryPointFormTextOutgoing($values, $generatedID, $storyPoint);
                break;
            case 'phone_call_incomming_voice' :
                $return = $this->RenderStoryPointFormPhoneCallVoiceIncomming($values, $generatedID, $storyPoint);
                break;
            case 'phone_call_outgoing_voice' :
                $return = $this->RenderStoryPointFormPhoneCallVoiceOutgoing($values, $generatedID, $storyPoint);
                break;
            case 'phone_call_hang_up' :
                // Hangs up any ongoing conversation
                break;
            case 'insert_news_item' :
                $return = $this->RenderStoryPointFormInsertNewsItem($values, $generatedID, $storyPoint);
                break;
            case 'start_new_thread' :
                $return = $this->RenderStoryPointFormStartNewThread($values, $generatedID, $storyPoint);
                break;
            case 'end_thread' :
                // End thread needs nothin' but a name. We're just ending this paricular game-thread
                break;
            case 'end_game' :
                $return = $this->RenderStoryPointFormEndGame($values, $generatedID, $storyPoint);
                break;
                
        }

        return $return;
    }

    private function RenderStoryPointFormTypeInputsType($type, $value, $generatedID, $inputName, $inputID) {
        
        // Now find out which type we will return
        $inputType = '';
        $inputExtraFeatures = '';
        $placeholderValue = 'Set new variable';

        switch($type) {
            case 'float':
                $inputType = 'number';
                $inputExtraFeatures = 'step=".01" min="0"';
                break;
            case 'number':
                $inputType = 'number';
                $inputExtraFeatures = 'step="1" min="0"';
                break;
            case 'text':
                $inputType = 'text';
                break;
            default : // If we have no type, then make it disabled
                $inputType = 'text';
                $inputExtraFeatures = 'disabled="disabled"';
                $placeholderValue = 'Choose variable first';
                break;
        }

        return '<input type="'.$inputType.'" '.$inputExtraFeatures.' value="'.$value.'" id="'.$generatedID.'_'.$inputID.'" name="'.$inputName.'" class="form-control auto-generated-input-type" placeholder="'.$placeholderValue.'" />';
    }

    public function RenderStoryPointFormTypeInputsAjax() {

        $data = $_POST['data'];
        $storyPointID = intval($data['story_point_id']);

        $storyPoint = StoryPoint::find($storyPointID);

        echo json_encode($this->RenderStoryPointFormTypeInputs($storyPoint, $data['generated_id']));
    }

    //public function RenderStoryPointFormTypeInputsChangeVariableValueInputAjax() {
    public function RenderStoryPointFormTypeInputsTypeAjax() {

        $data = $_POST['data'];
        
        $story_id = intval($data['story_id']);

        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        echo json_encode($this->RenderStoryPointFormTypeInputsType($data['variable_type'], '', $data['generated_id'], $data['input_name'], $data['input_id']));

        return;
    }

    private function RenderStoryPointFormTypeInputsWait($values, $generatedID) {
        
        $seconds = isset($values->seconds) && intval($values->seconds) > 0 ? intval($values->seconds) : 0;

        return '
        <div class="form-group">
            <label for="'.$generatedID.'_seconds">Seconds</label><br />
            <input type="number" min="0" id="'.$generatedID.'_seconds" name="json[seconds]" value="'.$seconds.'" class="form-control" />
        </div>
        ';
    }

    private function RenderStoryPointFormTypeInputsChangeVariable($values, $generatedID, StoryPoint $storyPoint) {
        
        $chosenVariableID   = isset($values->variable_id) && intval($values->variable_id) > 0 ? intval($values->variable_id) : 0;
        $chosenNewValue     = isset($values->new_value) && !empty($values->new_value) ? $values->new_value : '';

        $story = $storyPoint->story;
        // Go through the registered variables and list them
        $variables = $storyPoint->story->variables;

        $chosenVariable = $this->GetSpecificVariable($variables, $chosenVariableID);

        // Go through the found variables in order to create options
        $variableOptions = $this->GetAvailableVariables($story);

        return '
        <div class="form-group-container" data-input-name="json[value]" data-input-id="new_value">
            <div class="form-group">
                <input type="hidden" name="json[variable_id]" value="'.$chosenVariableID.'" class="form-control story-point-variable-choosen-variable" />
                <label for="'.$generatedID.'_variable_id">Choose variable</label><br />
                <input list="'.$generatedID.'_choose_variable" name="'.$generatedID.'_choose_variable" type="text" value="'.$chosenVariable['chosenVariableName'].'" class="form-control story-point-variable-choose-variable" placeholder="Search variable" />
                <datalist id="'.$generatedID.'_choose_variable">
                    '.$variableOptions.'
                </datalist>
            </div>
            <div class="form-group">
                <label for="'.$generatedID.'_new_value">Set new variable</label><br />
                <span class="story-point-variable-value-input">'.$this->RenderStoryPointFormTypeInputsType($chosenVariable['chosenVariableType'], $chosenNewValue, $generatedID, 'json[new_value]', 'new_value').'</span>
            </div>
        </div>
        ';
    }

    private function RenderStoryPointFormTypeVariableConditions($values, $generatedID, StoryPoint $storyPoint) {
        
        $return = '';

        // Does this storypoint lead anywhere? If not, then notify the user that it needs to lead somewhere in order to have options.
        if(!empty($storyPoint->leads_to_json)) {

            // Story point leads somewhere, create the options

            // Get the story
            $story = $storyPoint->story;

            // Get all the variables made for this story
            $variableOptions = $this->GetAvailableVariables($story);

            // Get all storypoints for this story-arch
            $storyPoints = $storyPoint->StoryArch->storyPoints;

            // Get leads to options
            $leadTos = json_decode($storyPoint->leads_to_json);
            $leadsToOptions = [];
            foreach($leadTos as $leadsTo) {

                // This can only lead to story-points so let's assume that

                if(is_object( $leadsTo) && property_exists($leadsTo, 'point')) {
                    $leadsToStoryPoint = $storyPoints->find($leadsTo->point);

                    $leadsToOptions[$leadsTo->point] = $leadsToStoryPoint->number.' '.$leadsToStoryPoint->name; 
                }
            }

            // Story point may or may not have filled out leads-tos in the instructions_json - get them
            
            // Foreach leads to create a
            $leadsTos = json_decode($storyPoint->leads_to_json);

            // Create a record for each leads_to in this story point
            $number = 0;
            foreach($leadsTos as $leadsToID) {

                // Create leads-to condition record
                $return .= $this->RenderStoryPointFormTypeVariableConditionRenderRecord($storyPoint, $values, (++$number), $variableOptions, $generatedID, $leadsToOptions);

            }
            // One more for the else fallback
            $return .= $this->RenderStoryPointFormTypeVariableConditionRenderRecord($storyPoint, $values, 0, $variableOptions, $generatedID, $leadsToOptions);

        } else {

            // Story point leads nowhere, tell the user that it needs to.
            $return = 'Story point leads nowhere yet, create leads in order to create the conditions. <br /><br />';
            
        }

        return $return;

    }

    private function RenderStoryPointFormTypeVariableConditionRenderRecord(StoryPoint $storyPoint, $values, $number, $variableOptions, $generatedID, $leadsTos) {

        $headline = $number == 0 
            ? 'Else'
            : (
                $number > 1
                    ? '#'.$number.' Else if'
                    : '#'.$number.' If'
            );

        // Collect the possible leads to options
        $leadsToOptions = '';
        foreach($leadsTos as $storyPointID => $concattedName) {
            $leadsToOptions .= '<option data-id="'.$storyPointID.'" value="'.$concattedName.'" />';
        }



        // So, do we have som values (from instructions_json)
        $chosenVariableID   = '';
        $chosenVariableName = '';
        $chosenVariableType = '';
        $chosenOperator     = '';
        $chosenValue        = '';
        $chosenLeadsToID    = '';
        $chosenLeadsToName  = '';
        if(is_object($values)) {

            // Okay, we have at least tome values - do we have any relevant for this record?
            if(property_exists($values, $number) && is_object($values->{$number})) {

                $instructions = $values->{$number};

                // We will fetch different variables if we're only looking at the fallback else statement
                if($number > 0) {
                    // We have settings for this - let's extract all the information we need

                    // Get the story
                    $story = $storyPoint->story;

                    // Get info about the variable in question
                    $variable = $this->GetSpecificVariable($story->variables, $instructions->variable);

                    // So, save all the neccesary information to pre-fill information in this record
                    $chosenVariableID   = property_exists($instructions, 'variable') ? $instructions->variable : '';;
                    $chosenVariableName = $variable['chosenVariableName'];
                    $chosenVariableType = $variable['chosenVariableType'];
                    $chosenOperator     = property_exists($instructions, 'operator') ? $instructions->operator : '';
                    $chosenValue        = property_exists($instructions, 'value') ? $instructions->value : '';
                }
                
                // Theese are needed everywhere, both in if statements and else statements
                $chosenLeadsToID    = $instructions->leads_to;
                $chosenLeadsToName  = isset($leadsTos[$instructions->leads_to]) ? $leadsTos[$instructions->leads_to] : '';
            }

        }

        // We'll build it differently if we're rendering the else fallback
        $variableAndOperatorGroup   = '';
        $valueInput                 = '';
        $elseHeadline               = '';
        $leadsToClass               = '';
        if($number > 0) {

            // Set the Variable and Operator Group
            $variableAndOperatorGroup = '
            <div class="form-group">
                <input type="hidden" name="json['.$number.'][variable]" value="'.$chosenVariableID.'" class="form-control story-point-variable-choosen-variable" />
                <label for="'.$generatedID.'_variable_id">'.$headline.'</label><br />
                <input
                    list="'.$generatedID.'_variable_condition_'.$number.'_variable"
                    name="'.$generatedID.'_variable_condition_'.$number.'"
                    type="text"
                    value="'.$chosenVariableName.'" 
                    class="form-control story-point-variable-choose-variable story-point-variable-condition-choose-variable"
                    placeholder="Search variable"
                />
                <datalist id="'.$generatedID.'_variable_condition_'.$number.'_variable">
                    '.$variableOptions.'
                </datalist>
                <span class="story-point-variable-condition-operator-section">
                    '.$this->RenderStoryPointFormTypeVariableConditionRenderRecordRenderOperator($chosenVariableType, $chosenOperator, $number, $generatedID).'
                </span>
            </div>
            ';

            // Set the value input
            $valueInput = '<span class="story-point-variable-value-input">'.$this->RenderStoryPointFormTypeInputsType($chosenVariableType, $chosenValue, $generatedID, 'json['.$number.'][value]', 'value_'.$number).'</span>';
            
        } else {
            $elseHeadline = '<label for="'.$generatedID.'_variable_condition_'.$number.'_leads_to">'.$headline.'</label><br />';

            $leadsToClass = ' story-point-choose-leads-to-full-length';
        }

        return '
        <div class="form-group-container" data-number="'.$number.'" data-input-name="json['.$number.'][value]" data-input-id="value">
            '.$variableAndOperatorGroup.$elseHeadline.'            
            <div class="form-group story-point-variable-condition-updatable-section">
                '.$valueInput.'
                <input type="hidden" name="json['.$number.'][leads_to]" value="'.$chosenLeadsToID.'" class="form-control story-point-chosen-leads-to" />
                <input
                    list="'.$generatedID.'_variable_condition_'.$number.'_leads_to_list"
                    type="text"
                    name="'.$generatedID.'_variable_condition_'.$number.'_leads_to"
                    id="'.$generatedID.'_variable_condition_'.$number.'_leads_to"
                    value="'.$chosenLeadsToName.'"
                    class="form-control story-point-choose-leads-to'.$leadsToClass.'"
                />
                <datalist id="'.$generatedID.'_variable_condition_'.$number.'_leads_to_list">
                    '.$leadsToOptions.'
                </datalist>
            </div>
        </div>
        ';

    }

        private function RenderStoryPointFormTypeVariableConditionRenderRecordRenderOperator($chosenVariableType, $value, $number, $generatedID) {
            $return = '
            <select name="json['.$number.'][operator]" id="'.$generatedID.'_variable_condition_'.$number.'_operator" value="'.$generatedID.'_variable_condition_'.$number.'_operator" class="form-control story-point-variable-condition-choose-operator">
            ';

            if($chosenVariableType == 'text') {
                $return .= '
                <option value="equals" '.($value == 'equals' ? 'selected' : '').'>=</option>
                ';
            } else {
                $return .= '
                <option value="equals" '.($value == 'equals' ? 'selected' : '').'>=</option>
                <option value="smaller" '.($value == 'smaller' ? 'selected' : '').'><</option>
                <option value="larger" '.($value == 'larger' ? 'selected' : '').'>></option>
                ';
            }

            $return .= '</select>';

            return $return;
        }

        public function RenderStoryPointFormTypeVariableConditionRenderRecordRenderOperatorAjax() {
            $data = $_POST['data'];

            echo json_encode($this->RenderStoryPointFormTypeVariableConditionRenderRecordRenderOperator($data['type'], '', $data['number'], $data['generated_id']));
        }

    private function RenderStoryPointFormTypeRedirect($values, $generatedID, StoryPoint $storyPoint) {

        $type           = is_object($values) && property_exists($values, 'type') ? $values->type : '';
        $redirectID     = is_object($values) && property_exists($values, 'redirect_id') ? intval($values->redirect_id) : '';
        $redirectValue  = '';

        // Do we have a redirectID? If so, then please write find the correct value
        if($redirectID > 0 && !empty($type)) {

            // So which type are are we looking at?
            switch($type) {
                case 'story_point' :
                    
                    // Get the correct storypoint
                    $leadsToStoryPoint = $storyPoint->story->storypoints->find($redirectID);

                    $redirectValue = $leadsToStoryPoint->name;

                    break;
                case 'story_arch' :

                    // Get the correct storypoint
                    $leadsToStoryArch = $storyPoint->story->storyarchs->find($redirectID);

                    $redirectValue = $leadsToStoryArch->name;

                    break;
            }

        }

        return '
        <div class="form-group">
            <label for="'.$generatedID.'_redirect_type">Redirect to</label><br />
            <select id="'.$generatedID.'_redirect_type" name="json[type]" class="form-control story-point-redirect-select-type">
                <option value="story_point" '.($type == 'story_point' ? 'selected' : '').'>Story point in this arch</option>
                <option value="story_arch" '.($type == 'story_arch' ? 'selected' : '').'>Story Arch</option>
            </select>
        </div>
        <div class="form-group">
            <input type="hidden" name="json[redirect_id]" value="'.$redirectID.'" class="form-control story-point-redirect-selected-id" />
            <label for="'.$generatedID.'_redirect_id">Choose destination</label><br />
            <input list="'.$generatedID.'_choose_destination" name="'.$generatedID.'_redirect_id" type="text" value="'.$redirectValue.'" class="form-control story-point-redirect-choose-destination" placeholder="Search destination" />
            <datalist id="'.$generatedID.'_choose_destination">
                '.$this->RenderStoryPointFormRedirectDestinationOptions($storyPoint, $type).'
            </datalist>
        </div>
        ';
    }

        private function RenderStoryPointFormRedirectDestinationOptions(StoryPoint $storyPoint, $type = '') {

            $options = '';
            switch($type) {
                case 'story_arch' :
                    
                    $options = $this->GetAvailableStoryArchs($storyPoint->story);

                    break;
                default :

                    $options = $this->GetAvailableStoryPoints($storyPoint->storyarch, [$storyPoint->id]);

                    break;
            }
            
            return $options;
        }

        public function RenderStoryPointFormRedirectTypeAjax() {
            $data = $_POST['data'];

            // Get the story
            $storyPoint = StoryPoint::find($data['story_point_id']);

            echo json_encode($this->RenderStoryPointFormRedirectDestinationOptions($storyPoint, $data['type']));
        }

    private function RenderStoryPointFormTextIncomming($values, $generatedID, StoryPoint $storyPoint) {

        $senderID   = is_object($values) && property_exists($values, 'from_character_id') ? $values->from_character_id : '';
        $message    = is_object($values) && property_exists($values, 'message') ? $values->message : '';

        // If we didn't find a $senderID, look at the latest story point that leads to this story point that is of the type of outgoing OR incomming message.
        // If we can find such a story point, and if it has a sender_id, then insert that as a suggestion to receiver
        if(empty($senderID)) {

            // Get character from previous story_point of text_incomming or text_outgoing
            $prevStoryPointFromOrToCharacterID    = intval($this->ExtractValueFromObject(
                ['to_character_id', 'from_character_id'],
                $this->GetInstructionJSONFromClosestRefererOfTypes($storyPoint, ['text_outgoing', 'text_incomming'])
            ));

            // Check if we set a from character in the previous StoryPoint
            if($prevStoryPointFromOrToCharacterID > 0)
                $senderID = $prevStoryPointFromOrToCharacterID;
        }

        // Do we have a redirectID? If so, then please write find the correct value
        $characterName = '';
        $character = '';
        if($senderID > 0) {

            $character = $storyPoint->story->characters->find($senderID);

            $characterName = $character->first_name.' '.$character->middle_names.' '.$character->last_name;
        }

        // Get settings (For time to write(reply))
        $settings = $this->GetStoryPointOrCharacterSettings($storyPoint, $character, true);

        return '
        <div class="form-group">
            <input type="hidden" name="json[from_character_id]" value="'.$senderID.'" class="form-control story-point-interlocutor-id" />
            <label for="'.$generatedID.'_sender_id">Message from</label><br />
            <input list="'.$generatedID.'_choose_sender" id="'.$generatedID.'_sender_id" data-storypoint-id="'.$storyPoint->id.'" name="'.$generatedID.'_sender_id" type="text" value="'.$characterName.'" class="form-control story-point-interlocutor-name" placeholder="Search character" />
            <datalist id="'.$generatedID.'_choose_sender">
                '.$this->GetAllCharacters($storyPoint->story).'
            </datalist>
        </div>
        <div class="form-group">
            <label for="'.$generatedID.'_message">Message</label><br />
            <textarea name="json[message]" id="'.$generatedID.'_message"" value="" class="form-control story-point-text-incomming-message">'.$message.'</textarea>
        </div>
        <div class="form-group">
            <label for="'.$generatedID.'_time_to_reply">Time to write (characters per minute)</label><br />
            <input type="number" id="'.$generatedID.'_time_to_reply" name="json[text_time_to_reply]" value="'.$settings->text_time_to_reply.'" class="form-control story-point-text-time-to-reply" />
        </div>
        ';
    }

        private function GetStoryPointOrCharacterSettings(StoryPoint $storyPoint, $character = '', $checkStoryPoint = false) {
            $handleSettings = new HandleSettings();

            $sendStoryPoint = $checkStoryPoint ? $storyPoint : '';

            return $handleSettings->GetSettings($storyPoint->story, 'character', $character, $sendStoryPoint);
        }

        public function GetStoryPointOrCharacterSettingsAjax() {

            $data = $_POST['data'];

            // Get the story
            $storyPoint = StoryPoint::find($data['story_point_id']);
            $character = $storyPoint->story->characters->find($data['character_id']);

            echo json_encode($this->GetStoryPointOrCharacterSettings($storyPoint, $character));

            return;
        }

    private function RenderStoryPointFormTextOutgoing($values, $generatedID, StoryPoint $storyPoint) {

        $receiverID   = is_object($values) && property_exists($values, 'to_character_id') ? $values->to_character_id : '';

        // Get the story
        $story = $storyPoint->story;

        // If we didn't find a receiverID, look at the latest story point that leads to this story point that is of the type of incomming message.
        // If we can find such a story point, and if it has a sender_id, then insert that as a suggestion to receiver
        if(empty($receiverID)) {

            // Get character from previous story_point of text_incomming or text_outgoing
            $prevStoryPointFromOrToCharacterID    = intval($this->ExtractValueFromObject(
                ['to_character_id', 'from_character_id'],
                $this->GetInstructionJSONFromClosestRefererOfTypes($storyPoint, ['text_outgoing', 'text_incomming'])
            ));

            // Check if we set a from character in the previous StoryPoint
            if($prevStoryPointFromOrToCharacterID > 0)
                $receiverID = $prevStoryPointFromOrToCharacterID;
        }
        
        $characterName = '';
        $character = '';
        if($receiverID  > 0) {

            $character = $story->characters->find($receiverID);

            $characterName = $character->first_name.' '.$character->middle_names.' '.$character->last_name;
        }

        // Get settings (For time to write(reply))
        $settings = $this->GetStoryPointOrCharacterSettings($storyPoint, $character, true);

        // Does this storypoint lead anywhere? If not, then notify the user that it needs to lead somewhere in order to have options.
        if(!empty($storyPoint->leads_to_json)) {

            // Story point leads somewhere, create the options
            
            // Go through them all and make sure the is a message
            $leadsTos = json_decode($storyPoint->leads_to_json);

            $leadsToMessages = '';
            foreach($leadsTos as $leadsTo) {

                // Get the story_point it leads_to
                $leadsToStoryPoint = $story->storypoints->find($leadsTo->point);

                $message = (is_object($values) && property_exists($values, $leadsTo->point) && is_object($values->{$leadsTo->point})) ? $values->{$leadsTo->point}->message : '';

                $leadsToMessages .= '
                <div class="form-group">
                    <label for="'.$generatedID.'_'.$leadsToStoryPoint->id.'_message">'.$leadsToStoryPoint->number.' '.$leadsToStoryPoint->name.' (message)</label><br />
                    <textarea name="json['.$leadsToStoryPoint->id.'][message]" id="'.$generatedID.'_'.$leadsToStoryPoint->id.'_message" class="form-control story-point-text-outgoing-message">'.$message.'</textarea>
                </div>
                ';
            }
        }
        else {
            // Story point leads nowhere, tell the user that it needs to.
            $leadsToMessages = 'Story point leads nowhere yet, create leads in order to create the conditions. <br /><br />';
        }

        return '
        <div class="form-group">
            <input type="hidden" name="json[to_character_id]" value="'.$receiverID.'" class="form-control story-point-interlocutor-id" />
            <label for="'.$generatedID.'_receiver_id">Message to</label><br />
            <input list="'.$generatedID.'_choose_receiver" id="'.$generatedID.'_receiver_id" data-storypoint-id="'.$storyPoint->id.'" name="'.$generatedID.'_receiver_id" type="text" value="'.$characterName.'" class="form-control story-point-interlocutor-name" placeholder="Search character" />
            <datalist id="'.$generatedID.'_choose_receiver">
                '.$this->GetAllCharacters($storyPoint->story).'
            </datalist>
        </div>
        '.$leadsToMessages.'
        <div class="form-group">
            <label for="'.$generatedID.'_time_before_read">Time before read (seconds)</label><br />
            <input type="number" id="'.$generatedID.'_time_before_read" name="json[text_time_before_read]" value="'.$settings->text_time_before_read.'" class="form-control story-point-text-time-before-read" />
        </div>
        <div class="form-group">
            <label for="'.$generatedID.'_time_to_read">Time To read (words per minute)</label><br />
            <input type="number" id="'.$generatedID.'_time_to_read" name="json[text_time_to_read]" value="'.$settings->text_time_to_read.'" class="form-control story-point-text-time-to-read" />
        </div>
        ';
    }

    private function RenderStoryPointFormPhoneCallVoiceIncomming($values, $generatedID, StoryPoint $storyPoint) {

        $senderID               = $this->ExtractValueFromObject(['from_character_id'], $values);
        $file                   = $this->ExtractValueFromObject(['file'], $values);
        $fileMime               = $this->ExtractValueFromObject(['mimetype'], $values);
        $allowedFormats         = new ValidFile(false, false, true);

        // If we didn't find a $senderID, look at the latest story point that leads to this story point that is of the type of outgoing OR incomming message.
        // If we can find such a story point, and if it has a sender_id, then insert that as a suggestion to receiver
        if(empty($senderID)) {

            // Get character from previous story_point of text_incomming or text_outgoing
            $prevStoryPointFromOrToCharacterID    = intval($this->ExtractValueFromObject(
                ['to_character_id', 'from_character_id'],
                $this->GetInstructionJSONFromClosestRefererOfTypes($storyPoint, ['phone_call_incomming_voice', 'phone_call_outgoing_voice'])
            ));

            // Check if we set a from character in the previous StoryPoint
            if($prevStoryPointFromOrToCharacterID > 0)
                $senderID = $prevStoryPointFromOrToCharacterID;
        }

        // Do we have a sender ID? If so, then please write find the correct value
        $characterName = '';
        if($senderID > 0) {

            $character = $storyPoint->story->characters->find($senderID);

            $characterName = $character->first_name.' '.$character->middle_names.' '.$character->last_name;
        }

        if($file && $fileMime) {
            $audioSection = '
            <br />
            <audio controls>
                <source src="/storage/stories/'.$storyPoint->story_id.'/storypoints/'.$storyPoint->id.'/'.$file.'" type="'.$fileMime.'">
                Your browser does not support the audio element.
            </audio>
            ';
        }
        else {
            $audioSection = '<br />Notice:<br /><small>'.$allowedFormats->message().'</small>';
        }

        return '
        <div class="story-point-phone-call-voice-incomming-updatable-section">
            <div class="form-group">
                <input type="hidden" name="json[from_character_id]" value="'.$senderID.'" class="form-control story-point-interlocutor-id" />
                <input type="hidden" name="json[file]" value="'.$file.'" class="form-control" />
                <input type="hidden" name="json[mimetype]" value="'.$fileMime.'" class="form-control" />
                <label for="'.$generatedID.'_sender_id">From</label><br />
                <input list="'.$generatedID.'_choose_sender" id="'.$generatedID.'_sender_id" data-storypoint-id="'.$storyPoint->id.'" name="'.$generatedID.'_sender_id" type="text" value="'.$characterName.'" class="form-control story-point-interlocutor-name" placeholder="Search character" />
                <datalist id="'.$generatedID.'_choose_sender">
                    '.$this->GetAllCharacters($storyPoint->story).'
                </datalist>
            </div>
            <div class="form-group">
                <label for="'.$generatedID.'_message_file">Voice message</label><br />
                <input type="file" name="file" id="'.$generatedID.'_message_file">
                '.$audioSection.'
            </div>
            '.$this->GeneratePhoneCallsIfUserHangUpFallbacks($values, $generatedID, $storyPoint).'
        </div>
        ';
    }

    private function RenderStoryPointFormPhoneCallVoiceOutgoing($values, $generatedID, StoryPoint $storyPoint) {

        $receiverID   = $this->ExtractValueFromObject(['to_character_id', 'from_character_id'], $values);

        // Get the story
        $story = $storyPoint->story;

        // If we didn't find a receiverID, look at the latest story point that leads to this story point that is of the type of incomming or outgoing voice.
        // If we can find such a story point, and if it has a sender_id, then insert that as a suggestion to receiver
        if(empty($receiverID)) {

            // Get character from previous story_point of text_incomming or text_outgoing
            $prevStoryPointFromOrToCharacterID    = intval($this->ExtractValueFromObject(
                ['to_character_id', 'from_character_id'],
                $this->GetInstructionJSONFromClosestRefererOfTypes($storyPoint, ['phone_call_incomming_voice', 'phone_call_outgoing_voice'])
            ));

            // Check if we set a from character in the previous StoryPoint
            if($prevStoryPointFromOrToCharacterID > 0)
                $receiverID = $prevStoryPointFromOrToCharacterID;
        }

        $characterName = '';
        if($receiverID  > 0) {

            $character = $story->characters->find($receiverID);

            $characterName = $character->first_name.' '.$character->middle_names.' '.$character->last_name;
        }

        // Does this storypoint lead anywhere? If not, then notify the user that it needs to lead somewhere in order to have options.
        if(!empty($storyPoint->leads_to_json)) {

            // Story point leads somewhere, create the options

            // Go through them all and make sure the is a message
            $leadsTos = json_decode($storyPoint->leads_to_json);

            $leadsToMessages = '';
            foreach($leadsTos as $leadsTo) {

                // Get the story_point it leads_to
                $leadsToStoryPoint = $story->storypoints->find($leadsTo->point);

                $message = (is_object($values) && property_exists($values, $leadsTo->point) && is_object($values->{$leadsTo->point})) ? $values->{$leadsTo->point}->message : '';

                $leadsToMessages .= '
                <div class="form-group">
                    <label for="'.$generatedID.'_'.$leadsToStoryPoint->id.'_message">'.$leadsToStoryPoint->number.' '.$leadsToStoryPoint->name.' (message)</label><br />
                    <textarea name="json['.$leadsToStoryPoint->id.'][message]" id="'.$generatedID.'_'.$leadsToStoryPoint->id.'_message" class="form-control story-point-text-outgoing-message">'.$message.'</textarea>
                </div>
                ';
            }
        }
        else {
            // Story point leads nowhere, tell the user that it needs to.
            $leadsToMessages = 'Story point leads nowhere yet, create leads in order to create the conditions. <br /><br />';
        }

        return '
        <div class="form-group">
            <input type="hidden" name="json[to_character_id]" value="'.$receiverID.'" class="form-control story-point-interlocutor-id" />
            <label for="'.$generatedID.'_receiver_id">Message to</label><br />
            <input list="'.$generatedID.'_choose_receiver" id="'.$generatedID.'_receiver_id" data-storypoint-id="'.$storyPoint->id.'" name="'.$generatedID.'_receiver_id" type="text" value="'.$characterName.'" class="form-control story-point-interlocutor-name" placeholder="Search character" />
            <datalist id="'.$generatedID.'_choose_receiver">
                '.$this->GetAllCharacters($storyPoint->story).'
            </datalist>
        </div>
        '.$leadsToMessages.'
        '.$this->GeneratePhoneCallsIfUserHangUpFallbacks($values, $generatedID, $storyPoint).'
        ';
    }

    private function GeneratePhoneCallsIfUserHangUpFallbacks($values, $generatedID, StoryPoint $storyPoint)
    {
        $redirectToArchIfHangUp = $this->ExtractValueFromObject(['if_user_hang_up_start_arch'], $values);
        $redirectToStoryPointAfterArchID = intval($this->ExtractValueFromObject(['if_user_hang_up_start_story_point'], $values));

        // If we didn't find a receiverID, look at the latest story point that leads to this story point that is of the type of incomming or outgoing voice.
        // If we can find such a story point, and if it has a $redirectToArchIfHangUp, then insert that as a suggestion to $redirectToArchIfHangUp
        if(empty($redirectToArchIfHangUp)) {

            // Get character from previous story_point of text_incomming or text_outgoing
            $redirectToArchIfHangUp    = intval($this->ExtractValueFromObject(
                ['if_user_hang_up_start_arch'],
                $this->GetInstructionJSONFromClosestRefererOfTypes($storyPoint, ['phone_call_incomming_voice', 'phone_call_outgoing_voice'])
            ));
        }

        $redirectToArchValue  = '';
        // Do we have a redirectID? If so, then please write find the correct value
        if($redirectToArchIfHangUp > 0) {

            // Get the correct story arch
            $leadsToStoryArch = $storyPoint->story->storyarchs->find($redirectToArchIfHangUp);

            $redirectToArchValue = $leadsToStoryArch->name;

        }
        else
        {
            $redirectToArchValue = ' -- Do nothing -- ';
        }

        // If we didn't find a receiverID, look at the latest story point that leads to this story point that is of the type of incomming or outgoing voice.
        // If we can find such a story point, and if it has a $redirectToArchIfHangUp, then insert that as a suggestion to $redirectToArchIfHangUp
        if(empty($redirectToStoryPointAfterArchID)) {

            // Get character from previous story_point of text_incomming or text_outgoing
            $redirectToStoryPointAfterArchID    = intval($this->ExtractValueFromObject(
                ['if_user_hang_up_start_story_point'],
                $this->GetInstructionJSONFromClosestRefererOfTypes($storyPoint, ['phone_call_incomming_voice', 'phone_call_outgoing_voice'], ['phone_call_hang_up'])
            ));
        }

        $redirectToStoryPointAfterArch  = '';
        // Do we have a redirectID? If so, then please write find the correct value
        if($redirectToStoryPointAfterArchID > 0) {

            // Get the correct storypoint
            $leadsToStoryPoint = $storyPoint->story->storypoints->find($redirectToStoryPointAfterArchID);

            $redirectToStoryPointAfterArch = $leadsToStoryPoint->name;

        }
        else
        {
            $redirectToStoryPointAfterArch = ' -- Do nothing -- ';
        }

        return '
        <div class="form-group">
            <input type="hidden" name="json[if_user_hang_up_start_arch]" value="'.$redirectToArchIfHangUp.'" class="form-control story-point-phone-call-hang-up-options-selected-id" />
            <label for="'.$generatedID.'_choose_arch_if_user_hangs_up">If user hangs up</label><br />
            <small>Start story arch</small>
            <input list="'.$generatedID.'_choose_destination_arch" name="'.$generatedID.'_choose_arch_if_user_hangs_up" type="text" value="'.$redirectToArchValue.'" class="form-control choose-story-point-phone-call-hang-up-options" placeholder="Search destination" />
            <datalist id="'.$generatedID.'_choose_destination_arch">
                <option data-id="0" value=" -- Do nothing -- " />
                '.$this->GetAvailableStoryArchs($storyPoint->story).'
            </datalist>
        </div>
        <div class="form-group">
            <input type="hidden" name="json[if_user_hang_up_start_story_point]" value="'.$redirectToStoryPointAfterArchID.'" class="form-control story-point-phone-call-hang-up-options-selected-id" />
            <small>After arch redirect to</small>
            <input list="'.$generatedID.'_choose_destination_story_point" name="'.$generatedID.'_choose_arch_if_user_hangs_up" type="text" value="'.$redirectToStoryPointAfterArch.'" class="form-control choose-story-point-phone-call-hang-up-options" placeholder="Search destination" />
            <datalist id="'.$generatedID.'_choose_destination_story_point">
                <option data-id="0" value=" -- Do nothing -- " />
                '.$this->GetAvailableStoryPoints($storyPoint->storyArch, [$storyPoint->id]).'
            </datalist>
        </div>
        ';
    }

    private function RenderStoryPointFormInsertNewsItem($values, $generatedID, StoryPoint $storyPoint) {

        $newsItemID   = is_object($values) && property_exists($values, 'news_item') ? intval($values->news_item) : '';

        // Do we have a news item ID
        $newsHeadline = '';
        if($newsItemID > 0) {

            // Get news item name
            $newsItem = $storyPoint->story->news->find($newsItemID);

            $newsHeadline = $newsItem->headline;
        }

        return '
        <div class="form-group">
            <input type="hidden" name="json[news_item]" value="'.$newsItemID.'" class="form-control story-point-insert-news-item-id" />
            <label for="'.$generatedID.'_news_item">Select news item</label><br />
            <input list="'.$generatedID.'_news_item_name" id="'.$generatedID.'_news_item" name="'.$generatedID.'_news_item_name" type="text" value="'.$newsHeadline.'" class="form-control story-point-insert-news-item-name" placeholder="Search news article" />
            <datalist id="'.$generatedID.'_news_item_name">
                '.$this->GetAllUnpublishedNews($storyPoint->story).'
            </datalist>
        </div>
        ';
    }

    private function RenderStoryPointFormStartNewThread($values, $generatedID, StoryPoint $storyPoint) {

        $redirectID     = intval($this->ExtractValueFromObject(['spawn_new_thread_arch'], $values));
        $redirectValue  = '';

        // Do we have a redirectID? If so, then please write find the correct value
        if($redirectID > 0) {

            // Get the correct storypoint
            $leadsToStoryArch = $storyPoint->story->storyarchs->find($redirectID);

            $redirectValue = $leadsToStoryArch->name;

        }

        return '
        <div class="form-group">
            <input type="hidden" name="json[spawn_new_thread_arch]" value="'.$redirectID.'" class="form-control story-point-start-new-thread-selected-id" />
            <label for="'.$generatedID.'_start_new_thread_id">Choose destination</label><br />
            <input list="'.$generatedID.'_choose_destination" name="'.$generatedID.'_start_new_thread_id" type="text" value="'.$redirectValue.'" class="form-control story-point-start-new-thread-choose-destination" placeholder="Search destination" />
            <datalist id="'.$generatedID.'_choose_destination">
                '.$this->GetAvailableStoryArchs($storyPoint->story).'
            </datalist>
        </div>
        ';
    }

    private function RenderStoryPointFormEndGame($values, $generatedID, StoryPoint $storyPoint) {
        return;

        return '
        <div class="form-group">
            <label for="'.$generatedID.'_end_game_message">Choose destination</label><br />
            <input list="'.$generatedID.'_choose_destination" name="'.$generatedID.'_start_new_thread_id" type="text" value="'.$redirectValue.'" class="form-control story-point-start-new-thread-choose-destination" placeholder="Search destination" />
            <datalist id="'.$generatedID.'_choose_destination">
                '.$this->GetAvailableStoryArchs($storyPoint->story).'
            </datalist>
        </div>
        ';
    }
    
    public function SavePost(StoryPoint $storyPoint, $number)
    {
        $storyPoint->story_id           =  intval($_POST['data']['story_id']);
        $storyPoint->story_arch_id      =  intval($_POST['data']['story_arch_id']);
        $storyPoint->name               =  'Unnamed #'.$number;
        $storyPoint->number             =  $number;
        $storyPoint->type               =  $_POST['data']['type'];
        $storyPoint->instructions_json  = "";
        $storyPoint->file               = "";
        $storyPoint->leads_to_json      = "";
        $storyPoint->save();
    }


    // Get stuff - should probably be disbursed between other files
    private function GetAvailableVariables(Story $story) {
        $variables = $story->variables;

        // Go through the found variables in order to create options
        $variableOptions = '';
        foreach($variables as $variable) {
            $variableOptions .= '<option data-id="'.$variable->id.'" data-type="'.$variable->type.'" value="'.$variable->{'key'}.'" />';
        }

        return $variableOptions;
    }

    private function GetSpecificVariable($variables, $chosenVariableID) {
        // So see if we found a pre-chosen variable? Will only happen if we're updating.
        $return = [
            'chosenVariableName' => '',
            'chosenVariableType' => ''
        ];
        if($chosenVariableID > 0) {

            // Prechosen variable found - let's see if it exists?
            $chosenVariable = $variables->find($chosenVariableID);

            if($chosenVariable) {
                // Prechosen Variable exists - save it in a variable
                $return['chosenVariableName'] = $chosenVariable->{'key'};
                $return['chosenVariableType'] = $chosenVariable->{'type'};
            }

        }

        return $return;
    }

    private function GetAvailableStoryArchs(Story $story) {
        $archs = $story->storyarchs->where('story_id', $story->id)->all();

        // Go through the found variables in order to create options
        $archOptions = '';
        foreach($archs as $arch) {
            $archOptions .= '<option data-id="'.$arch->id.'" value="'.$arch->name.'" />';
        }

        return $archOptions;
    }

    private function GetAvailableStoryPoints(StoryArch $storyArch, $excludes = []) {
        $storyPoints = $storyArch->storypoints->all();

        // Go through the found variables in order to create options
        $storyPointOptions = '';
        foreach($storyPoints as $storyPoint) {
            if(!in_array($storyPoint->id, $excludes))
                $storyPointOptions .= '<option data-id="'.$storyPoint->id.'" value="'.$storyPoint->name.'" />';
        }

        return $storyPointOptions;
    }

    private function GetAllCharacters(Story $story) {
        $characters = $story->characters->all();

        // Go through the found variables in order to create options
        $characterOptions = '';
        foreach($characters as $character) {
            $characterOptions .= '<option data-id="'.$character->id.'" value="'.$character->first_name.' '.$character->middle_names.' '.$character->last_name.'" />';
        }

        return $characterOptions;
    }

    private function GetAllUnpublishedNews(Story $story) {
        $news = $story->news->where('published', 0)->all();

        // Go through the found variables in order to create options
        $newsOptions = '';
        foreach($news as $newsItem) {
            $newsOptions .= '<option data-id="'.$newsItem->id.'" value="'.$newsItem->headline.'" />';
        }

        return $newsOptions;
    }

    /**
     * @param StoryPoint $storyPoint
     * @param array $types
     * @param array $stopIfArriveAt (Stop looking if we arrive at a story point of types)
     * @return mixed|null
     */
    private function GetInstructionJSONFromClosestRefererOfTypes(StoryPoint $storyPoint, array $types, array $stopIfArriveAt = []) {
        $referer = StoryPoint::where(
            'story_id', $storyPoint->story_id
        )->whereRaw(
            'JSON_CONTAINS(leads_to_json, \'{"point": '.$storyPoint->id.'}\')'
        )->orderBy(
            'id', 'ASC'
        )->first();


        $prevStoryPointInstructions = null;
        if(isset($referer->id) && $referer->id > 0) {

            if(is_countable($stopIfArriveAt) && count($stopIfArriveAt) > 0 && in_array($referer->type, $stopIfArriveAt)) {

                return '';

            } elseif(in_array($referer->type, $types)) { // Is the referer we found of the desired type?

                // Yes! We found a referer of the desired type!
                $prevStoryPointInstructions = json_decode($referer->instructions_json);

            } else {

                $prevStoryPointInstructions = self::GetInstructionJSONFromClosestRefererOfTypes($referer, $types);

            }
        }

        return $prevStoryPointInstructions;
    }
    private function ExtractValueFromObject($acceptableValues, $object = null) {

        $value = '';
        if(is_object($object)) {

            foreach ($acceptableValues as $acceptableValue) {

                if (property_exists($object, $acceptableValue) && !empty($object->{$acceptableValue})) {
                    $value = $object->{$acceptableValue};

                    break; // No need to look any further
                }
            }

        }

        return $value;
    }


    // Temporary printr
    private function printr($arr) {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }
}
