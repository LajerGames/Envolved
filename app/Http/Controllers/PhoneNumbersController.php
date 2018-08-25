<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\PhoneNumber;
use App\Common\Permission;
use App\Common\BuildSelectOptions;

class PhoneNumbersController extends Controller
{
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

        return view('stories.phone_numbers.index')->with('story', $story);
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

        $info = [
            'story' => $story,
            'characters_list' => $this->GenerateCharactersList($story, true)
        ];

        return view('stories.phone_numbers.create')->with('info', $info);
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

        $phoneNumber = new PhoneNumber;
        $this->SaveRequest($phoneNumber, $story_id, $request);

        return redirect('/stories/'.$story_id.'/phone_numbers')->with('success', 'Phone number created');
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
            return redirect('/stories/'.$story->id.'/phone_numbers')->with('error', 'Access denied');
        }

        $info = [
            'id'    => $id,
            'story' => $story,
            'characters_list' => BuildSelectOptions::Build($story->characters, 'id', ['first_name', 'middle_names', 'last_name'], ' ', 'None')
        ];

        return view('stories.phone_numbers.edit')->with('info', $info);
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
            return redirect('/stories/'.$story->id.'/phone_numbers')->with('error', 'Access denied');
        }

        $this->ValidateRequest($request);

        $phoneNumber = $story->phonenumber->find($id);

        $this->SaveRequest($phoneNumber, $story_id, $request);

        return redirect('/stories/'.$story_id.'/phone_numbers')->with('success', 'Number for; '.$phoneNumber->name.' updated');
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
        $phoneNumber = PhoneNumber::find($id);
        
        // Check for access
        if(
            !Permission::CheckOwnership(auth()->user()->id, $phoneNumber->story->user->id)
            || !Permission::CheckOwnership($phoneNumber->story->id, $phoneNumber->story_id)
        )
        {
            return redirect('/stories/'.$phoneNumber->story->id.'/phone_numbers')->with('error', 'Access denied');
        }

        $phoneNumber->delete();
        return redirect('/stories/'.$phoneNumber->story->id.'/phone_numbers')->with('success', 'Number; '.$phoneNumber->name.', deleted');
    }

    /**
     * Generating a characters array for selectbox
     */
    public function GenerateCharactersList($story, $addNone = false)
    {
        $characters = [];
        if($addNone)
            $characters[0] = ' -- None -- ';
        foreach($story->characters as $character)
        {
            $characters[$character->id] = $character->first_name.(empty($character->middle_names) ? '' : ' '.$character->middle_names).' '.$character->last_name;
        }

        return $characters;
    }

    /**
     * Validation for both store and update is the same, so we'll just call this method
     */
    public function ValidateRequest(Request $request)
    {
        $this->validate($request, [
            'number' => 'required',
            'name' => 'required'
        ]);
    }

    /**
     * Once Variable is instanciated or found (::find), the saving process is the same for both store and update
     */
    public function SaveRequest(PhoneNumber $phoneNumber, $story_id, Request $request)
    {
        $phoneNumber->story_id =  $story_id;
        $phoneNumber->character_id = "{$request->input('character_id')}";
        $phoneNumber->number = "{$request->input('number')}";
        $phoneNumber->name = "{$request->input('name')}";
        $phoneNumber->save();
    }
}
