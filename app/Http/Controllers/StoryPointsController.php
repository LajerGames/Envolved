<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Story;
use App\StoryPoint;
use App\Common\Permission;
use App\Common\GetNewestValues;

class StoryPointsController extends Controller
{
    /**
     * Insert new Story point
     *
     * @return \Illuminate\Http\Response
     */
    public function InsertStoryPoint()
    {
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

            $this->HandleReference($parentStoryPoint, $storyPoint->id);

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
    private function HandleReference(StoryPoint $storyPoint, $ref, $type = 'add') { 

        $leadsTo = [];
        if(!empty($storyPoint->leads_to_json)) {
            $leadsTo = (array)json_decode($storyPoint->leads_to_json);
        }

        switch($type) {
            case 'add' :
                // Let's add the $ref to the leads_to_json in the instructions_json
                if(!in_array($ref, $leadsTo)) {
                    $leadsTo[] = $ref;
                }
                break;
        }

        $storyPoint->leads_to_json = json_encode($leadsTo);

        $storyPoint->save();

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

            foreach($leadsToJson as $storyPointID) {

                // Get this particular story point
                $storyPoint = $story->StoryPoints->find(intval($storyPointID));

                // Get the color
                $color = config('constants.story_points')[$storyPoint->type][1];

                $return .= '<a class="id-number-circle story-point-leads-to-reference" data-story-point-no="'.$storyPoint->number.'" style="background-color:'.$color.'">'.$storyPoint->number.'</a>';

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
    public function SaveStoryPointForm()
    {
        // Initialize variables
        $postData = $_POST['data'];
        $story_id = intval($postData['story_id']);
        $story_point_id = intval($postData['story_point_id']);
        $data = [];
        parse_str($_POST['data']['data'], $data);
        $name = $data['name'];
        $json = json_encode($data['json']);

        // Get the story point
        $storyPoint = $this->GetStoryPoint($story_id, $story_point_id);

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
                    '.$startStoryPoint.'<u>'.ucfirst(str_replace('_', ' ', $storyPoint->type)).'</u>: '.$storyPoint->name.'
                </div>
                <div class="story-point-container-middle" style="border-left:1px solid '.$color.';border-right:1px solid '.$color.';">
                    <div class="story-point-form-container"></div>
                </div>
                <div class="story-point-container-bottom" style="background-color:'.$color.'">
                    <div class="story-pointleads-to-container">'.$this->GetStoryPointLeadsToMarkup($storyPoint, false).'</div>
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

        return '
        <form name="story_point">
            <input type="hidden" name="story_point_id" value="'.$storyPoint->id.'" />
            <div class="form-group">
                <label for="'.$generatedID.'_name">Name</label><br />
                <input type="text" id="'.$generatedID.'_name" name="name" value="'.$storyPoint->name.'" placeholder="Name" class="form-control" />
            </div>
            '.$this->RenderStoryPointFormTypeInputs($storyPoint, $generatedID).'
            <a href="javascript:void(0);" class="btn btn-default hastip add-story-point-to-this" data-moretext="Shortcut: <b>ctrl + shift + a</b>">Add story-point</a>
            <a href="javascript:void(0);" class="btn btn-primary pull-right hastip update-story-point" data-moretext="Shortcut: <b>ctrl + shift + u</b>">Update</a>
            <div class="clear-both"></div>
        </form>
        ';
    }

    // section: Form Type Inputs


    private function RenderStoryPointFormTypeInputs(StoryPoint $storyPoint, $generatedID) {
        $return = '';
        $values = json_decode($storyPoint->instructions_json);
//echo $storyPoint->type.' asdasdas';
        switch($storyPoint->type) {
            case 'change_variable' :
                $return = $this->RenderStoryPointFormTypeInputsChangeVariable($values, $generatedID, $storyPoint);
                break;
            case 'wait' :
                $return = $this->RenderStoryPointFormTypeInputsWait($values, $generatedID);
                break;
        }

        return $return;
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
        
        $seconds = isset($values->seconds) && intval($values->seconds) > 0 ? intval($values->seconds) : 0;

        // Go through the registered variables and list them
        $variables = $storyPoint->story->variables;
        $variableOptions = '';

        foreach($variables as $variable) {
            $variableOptions .= '<option data-id="'.$variable->id.'" data-type="'.$variable->type.'" data-generated-id="'.$generatedID.'" value="'.$variable->{'key'}.'" />';
        }

        return '
        <div class="form-group">
            <input type="hidden" name="json[variable-id]" value="0" class="form-control story-point-variable-choosen-variable" />
            <label for="'.$generatedID.'_variable_id">Choose variable</label><br />
            <input list="'.$generatedID.'_choose_variable" name="'.$generatedID.'_choose_variable" type="text" class="form-control story-point-variable-choose-variable" placeholder="Search variable" />
            <datalist id="'.$generatedID.'_choose_variable">
                '.$variableOptions.'
            </datalist>
        </div>
        <div class="form-group">
            <label for="'.$generatedID.'_new_value">Set new variable</label><br />
            <span class="story-point-variable-new-value">'.$this->RenderStoryPointFormTypeInputsChangeVariableValueInput($variable->type, $variable->value, $generatedID).'</span>
        </div>
        ';
    }

    private function RenderStoryPointFormTypeInputsChangeVariableValueInput($variableType, $value, $generatedID) {
    
        // Now find out which type we will return
        $inputType = '';
        $inputExtraFeatures = '';
        $placeholderValue = 'Set new variable';
        switch($variableType) {
            case 'number':
                $inputType = 'number';
                $inputExtraFeatures = 'step=".01" min="0"';
                break;
            case 'float':
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

        return '<input type="number" type="'.$inputType.'" '.$inputExtraFeatures.' value="'.$value.'" id="'.$generatedID.'_new_value" name="json[new_value]" class="form-control" placeholder="'.$placeholderValue.'" />';
    }

    public function RenderStoryPointFormTypeInputsChangeVariableValueInputAjax() {

        $data = $_POST['data'];
        
        $story_id = intval($data['story_id']);

        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        echo json_encode($this->RenderStoryPointFormTypeInputsChangeVariableValueInput($data['variable_type'], '', $data['generated_id']));

        return;
    }

    public function SavePost(StoryPoint $storyPoint, $number)
    {
        $storyPoint->story_id           =  intval($_POST['data']['story_id']);
        $storyPoint->story_arch_id      =  intval($_POST['data']['story_arch_id']);
        $storyPoint->name               =  'Unnamed #'.$number;
        $storyPoint->number             =  $number;
        $storyPoint->type               =  $_POST['data']['type'];
        $storyPoint->instructions_json  = "";
        $storyPoint->leads_to_json      = "";
        $storyPoint->save();
    }

    // Temporary printr
    private function printr($arr) {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }
}
