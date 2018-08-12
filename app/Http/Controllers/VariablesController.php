<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\Variable;
use App\Common\Permission;

class VariablesController extends Controller
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

            return view('stories.variables.index')->with('story', $story);
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

        return view('stories.variables.create')->with('story', $story);
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

        $variable = new Variable;
        $this->SaveRequest($variable, $story_id, $request);

        return redirect('/stories/'.$story_id.'/variables')->with('success', 'Variable created');
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
            return redirect('/stories/'.$story->id.'/variables')->with('error', 'Access denied');
        }

        $info = [
            'id'    => $id,
            'story' => $story
        ];

        return view('stories.variables.edit')->with('info', $info);
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
            return redirect('/stories/'.$story->id.'/variables')->with('error', 'Access denied');
        }

        $this->ValidateRequest($request);

        $variable = $story->variables->find($id);        

        $this->SaveRequest($variable, $story_id, $request);

        return redirect('/stories/'.$story_id.'/variables')->with('success', $variable->key.' updated');
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
        $variable = Variable::find($id);
        
        // Check for access
        if(
            !Permission::CheckOwnership(auth()->user()->id, $variable->story->user->id)
            || !Permission::CheckOwnership($variable->story->id, $variable->story_id)
        )
        {
            return redirect('/stories/'.$variable->story->id.'/variables')->with('error', 'Access denied');
        }

        $variable->delete();
        return redirect('/stories/'.$variable->story->id.'/variables')->with('success', 'Variable; '.$variable->key.', deleted');
    }

    /**
     * Validation for both store and update is the same, so we'll just call this method
     */
    public function ValidateRequest(Request $request)
    {
        $this->validate($request, [
            'key' => 'required',
            'value' => 'required'
        ]);
    }

    /**
     * Once Variable is instanciated or found (::find), the saving process is the same for both store and update
     */
    public function SaveRequest(Variable $variable, $story_id, Request $request)
    {
        $variable->story_id =  $story_id;
        $variable->type = "{$request->input('type')}";
        $variable->key = "{$request->input('key')}";
        $variable->value = "{$request->input('value')}";
        $variable->save();
    }
}
