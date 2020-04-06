<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\Settings;
use App\Character;
use App\Common\Permission;
use App\Common\HandleFiles;
use App\Common\HandleSettings;
use App\Rules\ValidFile;

class CharactersController extends Controller
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
     * @param  int  $story_id
     * @return \Illuminate\Http\Response
     */
    public function index($story_id)
    {
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        return view('stories.characters.index')->with('story', $story);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  int  $story_id
     * @return \Illuminate\Http\Response
     */
    public function create($story_id)
    {
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        // Get default settings
        $handleSettings = new HandleSettings();

        $settings = $handleSettings->GenerateDefaultSettings('character', [
            'tabs',
            'phone_time_between_call_logs_pregame',
            'text_time_between_texts_prestory',
            'photos_time_between_photos'
        ]);

        $info = [
            'story' => $story,
            'settings' => $handleSettings->GenerateDefaultSettings('character', [
                'tabs',
                'phone_time_between_call_logs_pregame',
                'text_time_between_texts_prestory',
                'photos_time_between_photos'
            ])
        ];

        return view('stories.characters.create')->with('info', $info);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $story_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($story_id, Request $request)
    {
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        $this->ValidateRequest($request);

        if($this->AlreadyHasProtagonist($story, $request->role))
            return redirect('/stories/'.$story->id.'/characters')->with('error', 'You can only have one protagonist');

        // Upload image
        $imageName = HandleFiles::UploadFile(
            $request,
            'avatar',
            'public/stories/'.$story_id.'/characters/'
        )['filename'];

        $character = new Character;
        $this->SaveRequest($character, $story_id, $imageName, $request, true);

        return redirect('/stories/'.$story_id.'/characters')->with('success', 'Character created');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $story_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($story_id, $id)
    {
        $story = Story::find($story_id);

        // Check for access
        if(
            !Permission::CheckOwnership(auth()->user()->id, $story->user->id)
            || !Permission::CheckOwnership($story_id, $story->id)
        )
        {
            return redirect('/stories/'.$story->id.'/characters')->with('error', 'Access denied');
        }

        $handleSettings = new HandleSettings();

        $settings = $handleSettings->GetSettings($story, 'character', $story->characters->find($id));

        $info = [
            'id'        => $id,
            'story'     => $story,
            'settings'  => $settings
        ];

        return view('stories.characters.edit')->with('info', $info);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @param  int  $story_id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, $story_id)
    {
        $story = Story::find($story_id);

        // Check for access
        if(
            !Permission::CheckOwnership(auth()->user()->id, $story->user->id)
            || !Permission::CheckOwnership($story_id, $story->id)
        )
        {
            return redirect('/stories/'.$story->id.'/characters')->with('error', 'Access denied');
        }

        $this->ValidateRequest($request);

        if($this->AlreadyHasProtagonist($story, $request->role, $id))
            return redirect('/stories/'.$story->id.'/characters')->with('error', 'You can only have one protagonist');

        $character = $story->characters->find($id);

        // Delete old and upload new image
        $imageName = HandleFiles::DeleteThenUpload(
            $request,
            $character,
            'avatar_url',
            'avatar',
            'public/stories/'.$story_id.'/characters/'
        )['filename'];

        $this->SaveRequest($character, $story_id, $imageName, $request);

        return redirect('/stories/'.$story_id.'/characters')->with('success', $character->first_name.' '.$character->last_name.' updated');
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
        $character = Character::find($id);
        
        // Check for access
        if(
            !Permission::CheckOwnership(auth()->user()->id, $character->story->user->id)
            || !Permission::CheckOwnership($character->story->id, $character->story_id)
        )
        {
            return redirect('/stories/'.$character->story->id.'/characters')->with('error', 'Access denied');
        }

        // Delete the image first if there is any
        HandleFiles::DeleteFile(
            'public/stories/'.$character->story->id.'/characters/'.$character->avatar_url,
            $character,
            'avatar_url'
        );

        if($_GET['deleteImageOnly'] == 'false')
        {
            $character->delete();
            return redirect('/stories/'.$character->story->id.'/characters')->with('success', 'Character; '.$character->first_name .' '.$character->last_name.', deleted');
        }
        else
        {
            return redirect('/stories/'.$character->story->id.'/characters/'.$id.'/edit')->with('success', 'Image deleted');
        }
    }

    /**
     * Validation for both store and update is the same, so we'll just call this method
     */
    public function ValidateRequest(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'gender' => 'required',
            'avatar' => [new ValidFile(true, false, false)]
        ]);
    }

    /**
     * Once Character is instanciated or found (::find), the saving process is the same for both store and update
     */
    public function SaveRequest(Character $character, $story_id, $imageName, Request $request, $isInsert = false)
    {
        $character->story_id =  $story_id;
        $character->in_contacts =  "{$request->input('in_contacts')}";
        $character->first_name = "{$request->input('first_name')}";
        $character->middle_names = "{$request->input('middle_names')}";
        $character->last_name = "{$request->input('last_name')}";
        $character->gender = "{$request->input('gender')}";
        $character->role = "{$request->input('role')}";
        $character->settings = json_encode($request->settings);
        if(!empty($imageName) || $isInsert)
            $character->avatar_url = "{$imageName}";

            

        $character->save();
    }

    public function AlreadyHasProtagonist(Story $story, $roleName, $updateID = 0)
    {
        $protagonist = $story->characters->where('role', 'protagonist')->first();
        $protagonistID = !empty($protagonist) ? intval($protagonist->id) : 0; 

        return $roleName == 'protagonist' && $protagonistID > 0 && $protagonistID != $updateID;
    }
}
