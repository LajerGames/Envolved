<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\Character;
use App\Common\Permission;
use App\Common\HandleImages;

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
        /*
        $characters = Character::where('story_id', $story_id)
            ->orderByRaw("FIELD(role , 'protagonist') DESC")
            ->paginate(10);
*/
        $story = Story::find($story_id);

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

        return view('stories.characters.create')->with('story', $story);
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

        // Upload image
        $imageName = HandleImages::UploadImage(
            $request,
            'avatar',
            'public/stories/'.$story_id.'/characters/'
        );          

        $character = new Character;
        $this->SaveRequest($character, $story_id, $imageName, $request);

        return redirect('/stories/'.$story_id.'/characters')->with('success', 'Story created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        
        $character->delete();

        return redirect('/stories/'.$character->story->id.'/characters')->with('success', 'Character; '.$character->first_name .' '.$character->last_name.', deleted');
    }

    /**
     * Validation for both store and update is the same, so we'll just call this method
     */
    public function ValidateRequest(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'avatar' => 'image|nullable|max:2000'
        ]);
    }

    /**
     * Once Character is instanciated or found (::find), the saving process is the same for both store and update
     */
    public function SaveRequest(Character $character, $story_id, $imageName, Request $request)
    {
        $character->story_id =  $story_id;
        $character->first_name = "{$request->input('first_name')}";
        $character->middle_names = "{$request->input('middle_names')}";
        $character->last_name = "{$request->input('last_name')}";
        $character->role = "{$request->input('role')}";
        if(!empty($imageName))
            $character->avatar_url = $imageName;
        $character->save();
    }
}
