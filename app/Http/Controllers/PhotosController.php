<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\Photo;
use App\Common\Permission;
use App\Common\HandleFiles;
use App\Rules\ValidFile;

class PhotosController extends Controller
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
        
        $photos = $story->photos;

        // We need to prefill the time field, let's say that now is the default time
        $time = !isset($_GET['pre-time']) ? now() : $_GET['pre-time'];
        // Also we need to prefill days ago with something. Let's just say that default is 0
        $daysAgo = !isset($_GET['days_ago']) ? 0 : $_GET['days_ago'];

        // Loop through all photos and find the newest ID, the time and days ago on that will be what we prefill with, unless we've received otherwise from GET
        $newestID = 0; // Use this variable to check which ID is the newer one.
        
        if(count($photos) > 0 && !isset($_GET['pre-time'])) {
            foreach($photos as $photo) {
                if($photo->id > $newestID) {
                    $time = $photo->time;
                    $daysAgo = $photo->days_ago;
                    $newestID = $photo->id;
                }
            }
        }

        $time = new \DateTime($time);
        $time->add(new \DateInterval('PT10M')); // Add 10 minutes so all pictures isn't taken at the same time!

        // Prepare an array to send to the view
        $info = [
            'story' => $story,
            'photos' => $photos,
            'days_ago' => $daysAgo,
            'time' => $time
        ];

        return view('stories.photos.index')->with('info', $info);
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
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        $this->ValidateRequest($request);

        // Upload image
        $imageName = HandleFiles::UploadFile(
            $request,
            'photo',
            'public/stories/'.$story_id.'/photos/'
        );

        $photo = new Photo;
        $this->SaveRequest($photo, $story_id, $imageName, $request);

        return redirect('/stories/'.$story_id.'/photos')->with('success', 'Photo inserted');
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
     * @param  int  $photo_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($story_id, $photo_id)
    {
        $story = Story::find($story_id);
        $photo = $story->photos->find($photo_id);

        if(
            !Permission::CheckOwnership(auth()->user()->id, $story->user_id)
            || !Permission::CheckOwnership($story_id, $story->id)
            || !Permission::CheckOwnership($photo->story_id, $story->id)
        )
            return redirect('/stories/'.$story->id.'/photos')->with('error', 'Access denied');
        
            HandleFiles::DeleteFile(
                'public/stories/'.$story_id.'/photos/'.$photo->image_name,
                $photo,
                'image_name'
            );

            $photo->delete();
            return redirect('/stories/'.$story->id.'/photos')->with('success', 'Photo deleted');
    }

    /**
     * Validation for both store and update is the same, so we'll just call this method
     */
    public function ValidateRequest(Request $request)
    {
        $this->validate($request, [
            'days_ago' => 'required',
            'time' => 'required',
            'photo' => ['required', new ValidFile(true, false)]
        ]);
    }
    
    /**
     * Save request for Photos
     */
    public function SaveRequest(Photo $photo, $story_id, $imageName, Request $request)
    {
        $photo->story_id =  $story_id;
        $photo->days_ago = "{$request->input('days_ago')}";
        $photo->time = "{$request->input('time')}";
        $photo->image_name = "{$imageName}";
        $photo->save();
    }
}
