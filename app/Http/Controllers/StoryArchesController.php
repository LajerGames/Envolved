<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Story;
use App\StoryArch;
use App\Common\Permission;



class StoryArchesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $story_id
     * @param  string  $tab_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($story_id, $tab_id, Request $request)
    {
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        $this->ValidateRequest($request);

        if(!$this->checkNumberAvailability($story, intval($request->input('number')))) {
            return redirect('/stories/'.$story_id.'/builder/handle')->withErrors('Please pick a number that does not already exist')->withInput();
        }

        $storyArch = new StoryArch;
        $this->SaveRequest($storyArch, $story_id, $request);

        return redirect('/stories/'.$story_id.'/builder/'.$request->input('tab'))->with('success', 'Story arch; '.$request->input('name').' created');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $story_id
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($story_id, $id, Request $request)
    {
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        $this->ValidateRequest($request);

        if(!$this->checkNumberAvailability($story, intval($request->input('number')), $id)) {
            return redirect('/stories/'.$story_id.'/builder/handle?id='.$id)->withErrors('Please pick a number that does not already exist')->withInput();
        }

        $storyArch = $story->storyarchs->find($id);
        $this->SaveRequest($storyArch, $story_id, $request);

        return redirect('/stories/'.$story_id.'/builder/'.$request->input('tab'))->with('success', 'Story arch; '.$request->input('name').' updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $story_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($story_id, $id)
    {
        $story = Story::find($story_id);
        
        // Check for access
        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        $storyArch = $story->storyarchs->find($id);
        $storyArch->delete();

        return redirect('/stories/'.$story_id.'/builder/'.$storyArch->tab_id)->with('success', 'Story arch; deleted');
    }

    /**
     * Validation for both store and update is the same, so we'll just call this method
     */
    public function ValidateRequest(Request $request)
    {
        $this->validate($request, [
            'number' => 'required|not_in:0',
            'tab' => 'required',
            'name' => 'required'
        ]);
    }

    public function SaveRequest(StoryArch $storyArch, $story_id, $request)
    {
        $storyArch->story_id =  $story_id;
        $storyArch->tab_id =  "{$request->input('tab')}";
        $storyArch->number =  intval($request->input('number'));
        $storyArch->name = "{$request->input('name')}";
        $storyArch->description = "{$request->input('description')}";
        $storyArch->save();
    }

    /**
     * Checks if the chosen number is available
     */
    private function checkNumberAvailability($story, $number, $archID = 0) {
        
        $existingArch = $story->storyarchs->where('number', $number);

        /**
         * Check if we found an storyArch with the chosen number
         * AND
         * If we're inserting and not updating that'll be enough, we know the number is taken.
         * If we're updating then check if the one we found is different from the one we're updating
         *      If so, then the number is occupied and we'll throw an error
         */
        if(
            !empty($existingArch->first())
            && isset($existingArch->first()->id)
            && (
                $archID == 0
                || (
                    $archID > 0
                    && $existingArch->first()->id != $archID
                )
            )
        ) {
            return false;
        }

        return true;
    }
}
