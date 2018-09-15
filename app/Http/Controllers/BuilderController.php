<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Story;
use App\Common\Permission;
use App\Common\HandleSettings;
use App\Common\GetNewestValues;

class BuilderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  int  $story_id
     * @param  string  $tab_id
     * @return \Illuminate\Http\Response
     */
    public function index($story_id, $tab_id)
    {
        $story = Story::find($story_id);

        if(
            !Permission::CheckOwnership(auth()->user()->id, $story->user_id)
        )
            return redirect('/stories')->with('error', 'Access denied');

        // Get settings
        $handleSettings = new HandleSettings();
        $settings = $handleSettings->GetSettings($story, 'editor');

        $storyArchs = $story->storyarchs->where('tab_id', $tab_id);

        // If we have an edit arch, find the info
        $editArch = '';
        $editID = 0;
        if(isset($_GET['id']) && intval($_GET['id']) > 0) {
            $editArch = $story->storyarchs->find(intval($_GET['id']));
            $editID = intval($_GET['id']);
        }
        
        $highestArchNumber = GetNewestValues::Build($story->storyarchs, ['number'], 'number');
        $nextArchNumber = intval($highestArchNumber['number'])+1;

        // highlight a story arch
        $highlightArchID = 0;
        if(isset($_GET['highlight']) && intval($_GET['highlight']) > 0) {
            $highlightArchID = intval($_GET['highlight']);
        }

        $info = [
            'story' => $story,
            'story_archs' => $storyArchs,
            'settings' => $settings,
            'show' => ($tab_id == 'handle' ? 'handle' : $tab_id),
            'highlight_arch_id' => $highlightArchID,
            'edit_id' => $editID,
            'edit_arch' => $editArch,
            'next_number' => $nextArchNumber
        ];
        
        return view('stories.builder.index')->with('info', $info);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
