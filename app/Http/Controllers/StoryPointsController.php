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
        $storyArchID        = intval($_POST['data']['story_arch_id']);
        $parentStoryPointID = intval($_POST['data']['parent_id']);
        $type               = $_POST['data']['type'];

        // So if we don't have a number, let's make it the newest one.
        $highestStoryPointNumber = GetNewestValues::Build($story->storyarchs->find($storyArchID)->storypoints, ['number'], 'number');
        $number = intval($highestStoryPointNumber['number'])+1;

        // So, we're inserting a new story point not updating one
        $storyPoint = new StoryPoint;
        $this->SavePost($storyPoint, $number);

        // Now check if parentStoryPointID is == 0, if it is, then it means that we must make this the new start story point of this arch
        if($parentStoryPointID == 0) {

            // Get the story arches controller
            $storyArchesController = new StoryArchesController();

            // Update the Story Point Start ID
            $storyArchesController->changeStoryPointStartID($story_id, $storyArchID, $storyPoint->id);

        } else  {

            $this->CreateReference($story, $parentStoryPointID, $storyPoint->id);

        }

        echo $storyPoint->id;
        return;
    }

    /**
     * Adds a story point ID to the references in instructions_json in another story point
     */
    private function CreateReference(Story $story, $refFrom, $refTo) {

        $storyPoint = $story->storyPoints->find($refFrom);

        $this->HandleReference($storyPoint, $refTo);

    }

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

        echo json_encode([
            'story_point_id' => $story_point_id,
            'story_point_html' =>  trim(preg_replace('/\s+/', ' ', $this->RenderStoryPointContainer($storyPoint, $story_id))) // Preg replace is to remove newlines
        ]);

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

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
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

        switch($storyPoint->type) {
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
}
