<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\Character;
use App\Common\Permission;

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
        
        $characters = Character::where('story_id', $story_id)
            ->orderByRaw("FIELD(role , 'protagonist') DESC")
            ->paginate(10);

            return view('stories.characters.index')->with('characters', $characters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        if(!$this->HasAccess(auth()->user()->id, $character->story->user->id, $character->story->id, $character->story_id))
            return redirect('/stories/'.$character->story->id.'/characters')->with('error', 'Access denied');
        
        $character->delete();

        return redirect('/stories/'.$character->story->id.'/characters')->with('success', 'Character; '.$character->first_names .' '.$character->last_name.', deleted');
    }

    /**
     * Checks if user has access to a method
     * Checks that the user owns the story and that the story owns the character.
     * 
     * @param  int  $user_id
     * @param  int  $story_user_id
     * @param  int  $story_id
     * @param  int  $character_story_id
     * @return boolean
     */
    public function HasAccess($user_id, $story_user_id, $story_id, $character_story_id)
    {

        print_r(func_get_args());
        $hasAccess = false;
        if($user_id == $story_user_id)
        {
            if($story_id == $character_story_id)
            {
                $hasAccess = true;
            }
        }
        return $hasAccess;
    }
}
