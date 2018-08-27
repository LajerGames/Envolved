<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\PhoneLog;
use App\Common\Permission;
use App\Common\HandleFiles;
use App\Common\BuildSelectOptions;
use App\Common\GetNewestValues;
use App\Rules\ValidFile;

class PhoneLogsController extends Controller
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

        if(
            !Permission::CheckOwnership(auth()->user()->id, $story->user_id)
        )
            return redirect('/stories')->with('error', 'Access denied');

        // Get Phone Logs
        $phoneLogs = $story->phonelogs;

        // Get phone numbers (sorry about the singular variable)
        $phoneNumbers = $story->phonenumber;

        // Generate the array for the phone numbers select
        $phoneNumbersSelect = BuildSelectOptions::Build($phoneNumbers, 'id', ['name', 'number'], ', ', 'Select');

        // We need to prefill the days ago and the time with some default data
        $daysAgo = 0;
        $time = now();
        $minutesToAdd = 2; // We might need to add a certain amount of minutes to the time for the next entry, remember, we're building a prefill to make data entry easier. Let's start off with adding 2 minutes, might be more!

        // Look through the data and find the data entered in the most recent entry of the phoneLogs model. That's what we'll use to prefill unless we're told otherwise
        $mostRecentData = GetNewestValues::Build($phoneLogs, ['days_ago', 'start_time', 'minutes']);

        // Did we find anything?
        if(strlen($mostRecentData['days_ago']) > 0) {
            $daysAgo = $mostRecentData['days_ago'];
            $time = $mostRecentData['start_time'];
            $minutesToAdd += $mostRecentData['minutes'];
        }

        // Let's add the amount of minutes to the $time to finish the build of the time to prefill.
        $time = new \DateTime($time);
        $time->add(new \DateInterval('PT'.$minutesToAdd.'M'));

        // Make array to send along to the view
        $info = [
            'story' => $story,
            'phone_logs' => $phoneLogs,
            'phone_numbers_select' => $phoneNumbersSelect,
            'time' => $time,
            'days_ago' => $daysAgo
        ];

        return view('stories.phone_logs.index')->with('info', $info);
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

        $phoneLog = new PhoneLog;
        $this->SaveRequest($story_id, $phoneLog, $request);

        return redirect('/stories/'.$story_id.'/phonelogs')->with('success', 'Phonelog inserted');
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

    /**
     * Validation for both store and update is the same, so we'll just call this method
     */
    public function ValidateRequest(Request $request)
    {
        $this->validate($request, [
            'phone_number_id' => 'required',
            'days_ago' => 'required',
            'time' => 'required',
            'minutes' => 'required',
            'direction' => 'required'
        ]);
    }

    /**
     * Save request for PhoneLog
     */
    public function SaveRequest($story_id, PhoneLog $phoneLog, Request $request)
    {
        $phoneLog->story_id =  $story_id;
        $phoneLog->phone_number_id = "{$request->input('phone_number_id')}";
        $phoneLog->days_ago = "{$request->input('days_ago')}";
        $phoneLog->start_time = "{$request->input('time')}";
        $phoneLog->minutes = "{$request->input('minutes')}";
        $phoneLog->direction = "{$request->input('direction')}";
        $phoneLog->save();
    }
}
