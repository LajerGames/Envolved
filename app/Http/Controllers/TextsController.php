<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\PhoneNumber;
use App\Text;
use App\Common\Permission;
use App\Common\HandleFiles;
use App\Common\GetNewestValues;
use App\Common\HandleSettings;
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

        // TODO: Image upload does not work.
        // Folder is not created, but when it is created it still does not work. How to do it?
        // Upload image/* or video*/
        $fileName = HandleFiles::UploadFile(
            $request,
            'mms',
            '/public/stories/'.$story_id.'/texts/'
        );

        // If we have a image /*or a video*/ it will be saved as two texts text first image/video after
        if(!empty($request->input('text')))
        {
            $text2 = new Text;
            $this->SaveRequest($text2, $phone_number_id, $story_id, '', $request);
        }
        if(!empty($fileName))
        {
            $text1 = new Text;
            $this->SaveRequest($text1, $phone_number_id, $story_id, $fileName, $request);
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
     * This is a little gay! You can enter this method two ways.
     * 1. When looking texts for a phone number
     * 2. When trying to edit a specific text, that will provide a third argument
     *
     * @param  int  $story_id
     * @param  int  $phone_number_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($story_id, $phone_number_id, $id = 0)
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

        // Get settings
        $handleSettings = new HandleSettings();
        $settings = $handleSettings->GetSettings($story, 'editor');

        // Find relevant variables
        $phoneNumber = $story->phonenumber->find($phone_number_id);
        $texts = $phoneNumber->texts; // All the texts
        $text = $id > 0 ? $texts->find($id) : ''; // The text we're currently editing if we're editing
        $daysAgo = 0;
        $time = now();

        // Look through the data and find the data entered in the most recent entry of the phoneLogs model. That's what we'll use to prefill unless we're told otherwise
        $mostRecentData = GetNewestValues::Build($texts, ['days_ago', 'time']);

        // Did we find anything?
        if(strlen($mostRecentData['days_ago']) > 0) {
            $daysAgo = $mostRecentData['days_ago'];
            $time = $mostRecentData['time'];
        }

        $time = new \DateTime($time);
        $time->add(new \DateInterval('PT'.$settings->text_time_between_texts_prestory.'M'));

        $info = [
            'phone_number_id'   => $phone_number_id,
            'story'             => $story,
            'phone_number'      => $phoneNumber,
            'texts'             => $texts,
            'text'              => $text,
            'time'              => $time,
            'days_ago'          => $daysAgo,
            'edit_id'           => $id
        ];

        return view('stories.texts.edit')->with('info', $info);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $story_id
     * @param  int  $phone_number_id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $story_id, $phone_number_id)
    {
        // Text id to edit
        $id = intval($_GET['id']);
        
        $story = Story::find($story_id);
        $phoneNumber = $story->phonenumber->find($phone_number_id);
        $text = $phoneNumber->texts->find($id);

        if(
            !Permission::CheckOwnership(auth()->user()->id, $story->user_id)
            || !Permission::CheckOwnership($story_id, $story->id)
            || !Permission::CheckOwnership($phoneNumber->story_id, $story_id)
            || !Permission::CheckOwnership($text->phone_number_id, $phoneNumber->id)
        )
            return redirect('/stories/'.$story->id.'/texts/'.$phoneNumber->id.'/edit')->with('error', 'Access denied');

        $this->ValidateRequest($request);

        // Delete, then upload image/* or video*/
        $fileName = HandleFiles::DeleteThenUpload(
            $request,
            $text,
            'filename',
            'mms',
            'public/stories/'.$story_id.'/texts/'
        );

        // On an update we can only update a file or a text, so which is it?
        if(!empty($request->input('text')))
        {
            $this->SaveRequest($text, $phone_number_id, $story_id, '', $request);
        }
        elseif(!empty($fileName) || !empty($text->filename))
        {
            $this->SaveRequest($text, $phone_number_id, $story_id, $fileName, $request);
        }

        return redirect('/stories/'.$story_id.'/texts/'.$phone_number_id.'/edit')->with('success', 'Text message updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $story_id
     * @param  int  $phone_number_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($story_id, $phone_number_id)
    {
        $id = intval($_GET['textID']);

        $story = Story::find($story_id);
        $phoneNumber = $story->phonenumber->find($phone_number_id);
        $text = $phoneNumber->texts->find($id);

        if(
            !Permission::CheckOwnership(auth()->user()->id, $story->user_id)
            || !Permission::CheckOwnership($story_id, $story->id)
            || !Permission::CheckOwnership($phoneNumber->story_id, $story_id)
            || !Permission::CheckOwnership($text->phone_number_id, $phoneNumber->id)
        )
            return redirect('/stories/'.$story->id.'/texts/'.$phoneNumber->id.'/edit')->with('error', 'Access denied');

            HandleFiles::DeleteFile(
                'public/stories/'.$story_id.'/texts/'.$text->filename,
                $text,
                'filename'
            );

            $text->delete();
            return redirect('/stories/'.$story->id.'/texts/'.$phoneNumber->id.'/edit')->with('success', 'Text deleted');
    }

    /**
     * Validation for both store and update is the same, so we'll just call this method
     */
    public function ValidateRequest(Request $request)
    {
        $this->validate($request, [
            'sender' => 'required',
            'mms' => [new ValidFile(true, false)],
            'time' => 'required'
        ]);
    }

    /**
     * Once Text is instanciated or found (::find), the saving process is the same for both store and update
     */
    public function SaveRequest(Text $text, $phone_number_id, $story_id, $fileName, Request $request)
    {
        $text->story_id =  $story_id;
        $text->phone_number_id =  $phone_number_id;
        $text->is_seen = 1;
        $text->sender = "{$request->input('sender')}";

        // If we have an image or a file we will ignore the text
        if(!empty($fileName) || !empty($text->filename))
        {
            /**
             * One of two things could happen in here.
             * Either we have a file in $request->file or we don't
             * IF WE DO HAVE A FILE (REF 1)
             * That means that we're either updating or inserting a file into a text,
             * IF WE DON'T HAVE A FILE (REF 1)
             * That means that the user is updating a text with a file in it, but the user has not
             * uploaded a new file. Since we need to update whatever else we're asked to update
             * then just reinsert the old data
             * NOTICE: There might be a better way of doing this, but for now, if I don't insert all fields
             * then it will fail. If you can be asked at some point, then please find out how just to set a
             * few fields and all of this nonsense could be avoided.
             * HINT: I think it has to do with validation and nullable - but that's just a guess
             */

             if(!empty($fileName)) {
                // Read REF 1 above
                $text->text = "";
                $text->filename = $fileName;
                $text->filetype = explode('/', $request->file('mms')->getMimeType())[0];
                $text->filemime = $request->file('mms')->getMimeType();
             } else {
                // Read REF 2 above
                $text->text = "";
                $text->filename = $text->filename;
                $text->filetype = $text->filetype;
                $text->filemime = $text->filemime;
             }
        }
        else
        {
            $text->text = "{$request->input('text')}";
            $text->filename = '';
            $text->filetype = '';
            $text->filemime = '';
        }
        $text->days_ago = intval($request->input('days_ago'));
        $text->time = "{$request->input('time')}";
        $text->save();
    }
}
