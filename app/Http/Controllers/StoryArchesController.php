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

        $storyArch = new StoryArch;
        $this->SaveRequest($storyArch, $story_id, $request);

        return redirect('/stories/'.$story_id.'/builder/'.$request->input('tab'))->with('success', 'Story arch; '.$request->input('name').' created');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Validation for both store and update is the same, so we'll just call this method
     */
    public function ValidateRequest(Request $request)
    {
        $this->validate($request, [
            'tab' => 'required',
            'name' => 'required'
        ]);
    }

    public function SaveRequest(StoryArch $storyArch, $story_id, $request)
    {
        $storyArch->story_id =  $story_id;
        $storyArch->tab_id =  "{$request->input('tab')}";
        $storyArch->name = "{$request->input('name')}";
        $storyArch->description = "{$request->input('description')}";
        $storyArch->save();
    }
}
