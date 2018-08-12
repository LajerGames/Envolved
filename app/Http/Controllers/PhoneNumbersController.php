<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\PhoneNumber;
use App\Common\Permission;

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
}
