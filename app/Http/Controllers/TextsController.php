<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\PhoneNumber;
use App\Text;
use App\Common\Permission;
use App\Common\HandleFiles;
use App\Rules\ValidFile;

class TextsController extends Controller
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

        return view('stories.texts.index')->with('story', $story);
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
     * @param  int  $story_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($story_id, Request $request)
    {
        $phone_number_id = intval($_GET['phone_number_id']);

        $story = Story::find($story_id);
        $phoneNumber = $story->phonenumber->find($phone_number_id);

        if(
            !Permission::CheckOwnership(auth()->user()->id, $story->user_id)
            || !Permission::CheckOwnership($story_id, $story->id)
            || !isset($phoneNumber->number)
        )
            return redirect('/stories')->with('error', 'Access denied');

        $this->ValidateRequest($request);

        // Upload image or video
        $fileName = HandleFiles::UploadFile(
            $request,
            'mms',
            'public/stories/'.$story_id.'/texts/'
        );

        // If we have a image or a videoit will be saved as two texts text first image/video after
        if(!empty($request->input('text')))
        {
            $text2 = new Text;
            $this->SaveRequest($text2, $phone_number_id, '', $request);
        }
        if(!empty($fileName))
        {
            $text1 = new Text;
            $this->SaveRequest($text1, $phone_number_id, $fileName, $request);
        }
        
        return redirect('/stories/'.$story_id.'/texts/'.$phone_number_id.'/edit')->with('success', 'Text message created');
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
            return redirect('/stories/'.$story->id.'/texts')->with('error', 'Access denied');
        }

        $info = [
            'id'    => $id,
            'story' => $story
        ];

        return view('stories.texts.edit')->with('info', $info);
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
            'sender' => 'required',
            'mms' => [new ValidFile()]
        ]);
    }

    /**
     * Once Text is instanciated or found (::find), the saving process is the same for both store and update
     */
    public function SaveRequest(Text $text, $phone_number_id, $fileName, Request $request)
    {
        $text->phone_number_id =  $phone_number_id;
        $text->is_seen = 1;
        $text->seen_on = "{$request->input('sent_on')}"; // Using sent on when creating historical texts
        $text->sender = "{$request->input('sender')}";

        // If we have an image or a file we will ignore the text
        if(!empty($fileName))
        {
            $text->text = "";
            $text->filename = $fileName;
            $text->filetype = explode('/', $request->file('mms')->getMimeType())[0];
            $text->filemime = $request->file('mms')->getMimeType();
        }
        else
        {
            $text->text = "{$request->input('text')}";
            $text->filename = '';
            $text->filetype = '';
            $text->filemime = '';
        }
        $text->sent_on = "{$request->input('sent_on')}";
        $text->save();
    }
}
