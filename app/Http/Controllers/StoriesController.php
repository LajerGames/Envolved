<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\Settings;
use App\Common\Permission;
use App\Common\HandleSettings;
use App\Common\HandleFiles;
use DB;

class StoriesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = auth()->user()->id;
        $stories = Story::where('user_id', $user_id)
            ->where('backup_of_story', 0)
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('stories.index')->with('stories', $stories);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('stories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->ValidateRequest($request);

        $story = new Story;
        $settings = new Settings;
        $this->SaveRequest($story, $request, $settings);

        return redirect('/stories')->with('success', 'Story created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $story = Story::find($id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        return view('stories.show')->with('story', $story);
    }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return redirect('/stories/'.$id.'/builder');
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
        $this->ValidateRequest($request);

        $story = Story::find($id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        $this->SaveRequest($story, $request);

        return redirect('/stories')->with('success', 'Story updated'); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $story = Story::find($id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        # Characters
        $this->LoopAndDestroy($story->characters);

        # News
        $this->LoopAndDestroy($story->news);

        # Phone Logs
        $this->LoopAndDestroy($story->phonelogs);

        # Phone Numbers
        $this->LoopAndDestroy($story->phonenumber);

        # Phone Number Texts
        $this->LoopAndDestroy($story->texts);

        # Phone Photos
        $this->LoopAndDestroy($story->photos);

        # Phone Settings
        $this->LoopAndDestroy($story->settings);

        # Phone Variables
        $this->LoopAndDestroy($story->variables);

        # Story Archs
        $this->LoopAndDestroy($story->storyarchs);

        # Story Points
        $this->LoopAndDestroy($story->storypoints);

        # Files
        $handleFiles = new HandleFiles();
        $handleFiles->deleteDir(public_path().'/storage/stories/'.$story->id);

        # Story
        $story->delete();

        return;

        //return redirect('/stories')->with('success', $story->title .' deleted');
    }

    private function LoopAndDestroy($object) {

        if($object instanceof \Illuminate\Database\Eloquent\Model) {

            // This is a model
            $this->DestroyModel($object);

        } else {

            // This is a collection - loop through it and destroy
            if(!empty($object)) {
                foreach($object as $model) {
                    $this->DestroyModel($model);
                }
            }

        }

    }

    private function DestroyModel($model) {

        $model->delete();

        return;

    }

    /**
     * Validation for both store and update is the same, so we'll just call this method
     */
    public function ValidateRequest(Request $request)
    {
        $this->validate($request, [
            'title' => 'required'
        ]);
    }

    /**
     * Once Story is instanciated or found (::find), the saving process is the same for both store and update
     */
    public function SaveRequest(Story $story, Request $request, $settings = '')
    {
        $story->user_id = auth()->user()->id;
        $story->title = $request->input('title');
        $story->backup_of_story = 0;
        $story->backup_name = '';
        $story->backup_confirmed = 0;
        $story->short_description = $request->input('short_description');
        $story->description = $request->input('description');
        $story->save();

        if($settings instanceof Settings) {

            // Get default settings
            $handleSettings = new HandleSettings();

            $settings->story_id = $story->id;
            $settings->story_settings = json_encode($handleSettings->GenerateDefaultSettings('story'));
            $settings->editor_settings = json_encode($handleSettings->GenerateDefaultSettings('editor'));
            $settings->save();

        }
    }
}
