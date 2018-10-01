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
use App\Common\HandleSettings;
use App\Rules\DoubleLog;

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

        // Get settings
        $handleSettings = new HandleSettings();
        $settings = $handleSettings->GetSettings($story, 'editor');

        // Get Phone Logs
        $phoneLogs = $story->phonelogs;

        // Get phone numbers (sorry about the singular variable)
        $phoneNumbers = $story->phonenumber;

        // Generate the array for the phone numbers select
        $phoneNumbersSelect = BuildSelectOptions::Build($phoneNumbers, 'id', ['name', 'number'], ', ', 'Select');

        // We need to prefill the days ago and the time with some default data
        $daysAgo = 0;
        $time = now();
        $minutesToAdd = $settings->phone_time_between_call_logs_pregame; // We might need to add a certain amount of minutes to the time for the next entry, remember, we're building a prefill to make data entry easier.

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

        // Let's give the phone log we're saving right now a datetime from and to
        $toAndFrom = $this->CreateToAndFrom($request->input('time'), $request->input('minutes'));
        
        // Get all phonelogs from the particular day we're saving a new log to, to make sure we have no "double talk" :)
        $phoneLogs = PhoneLog::where('days_ago', intval($request->input('days_ago')))->get();
        $interferenceInfo = [];
        foreach($phoneLogs as $phoneLog) {
            
            // Convert to datetime objects
            $checkToAndFrom = $this->CreateToAndFrom($phoneLog->start_time, $phoneLog->minutes);

            if($toAndFrom['from'] > $checkToAndFrom['from'] && $toAndFrom['from'] < $checkToAndFrom['to']) {
                $interferenceInfo = $this->CreateInterferenceInfo($phoneLog, $checkToAndFrom, $toAndFrom);
                break;
            } elseif($toAndFrom['to'] > $checkToAndFrom['from'] && $toAndFrom['to'] < $checkToAndFrom['to']) {
                $interferenceInfo = $this->CreateInterferenceInfo($phoneLog, $checkToAndFrom, $toAndFrom);
                break;
            } elseif($checkToAndFrom['from'] > $toAndFrom['from'] && $checkToAndFrom['from'] < $toAndFrom['to']) {
                $interferenceInfo = $this->CreateInterferenceInfo($phoneLog, $checkToAndFrom, $toAndFrom);
                break;
            } elseif($checkToAndFrom['to'] > $toAndFrom['from'] && $checkToAndFrom['to'] < $toAndFrom['to']) {
                $interferenceInfo = $this->CreateInterferenceInfo($phoneLog, $checkToAndFrom, $toAndFrom);
                break;
            }
        }

        if(count($interferenceInfo) > 0) {
 
            // let's create an empty validator, assuming that we have no any errors yet
            $validator = \Validator::make([], []);
            $validator->getMessageBag()->add('time', '
                <strong>Error:</strong><br />
                Your saved time interferes with another log.<br />
                You\'re trying save a log from <strong>'.$interferenceInfo['days_ago'].'</strong> days ago<br />
                <span class="indent-20">from: <strong>'.$interferenceInfo['chosen_start_time'].'</strong> to <strong>'.$interferenceInfo['chosen_end_time'].'</strong>.</span><br />
                It appears that the following phone log, also from <strong>'.$interferenceInfo['days_ago'].'</strong> days ago is interfering<br />
                <span class="indent-20">from: <strong>'.$interferenceInfo['interference_start_time'].'</strong> to <strong>'.$interferenceInfo['interference_end_time'].'</strong>.</span><br />
            ');
            return \Redirect::back()->withErrors($validator)->withInput();

        }

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
     * @param  int  $story_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($story_id, $id)
    {
        $phoneLog = PhoneLog::find($id);

        if(!Permission::CheckOwnership(auth()->user()->id, $phoneLog->story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

            $phoneLog->delete();
        return redirect('/stories/'.$phoneLog->story->id.'/phonelogs')->with('success', 'phonelog deleted');
    }

    /**
     * Creates two date objects (From and to) based on time, and minutes (duration)
     */
    private function CreateToAndFrom($time, $minutes)
    {
        $from = new \DateTime(date('Y-m-d '.$time));
        $to = clone $from;
        $to->add(new \DateInterval('PT'.$minutes.'M'));

        return [
            'from' => $from,
            'to' => $to
        ];
    }

    /**
     * Returns an array with relevant info about the item that interfered with what we're trying to save
     */
    private function CreateInterferenceInfo(PhoneLog $phoneLog, array $interferenceDateTimes, array $chosenDateTimes) {
        return [
            'days_ago' => $phoneLog->days_ago,
            'interference_start_time' => $interferenceDateTimes['from']->format('H:i'),
            'interference_end_time' => $interferenceDateTimes['to']->format('H:i'),
            'chosen_start_time' => $chosenDateTimes['from']->format('H:i'),
            'chosen_end_time' => $chosenDateTimes['to']->format('H:i')
        ];
    }

    /**
     * Validation for both store and update is the same, so we'll just call this method
     */
    public function ValidateRequest(Request $request)
    {
        $this->validate($request, [
            'phone_number_id' => 'required|not_in:0',
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
        $phoneLog->answered = "{$request->input('answered')}";
        $phoneLog->save();
    }
}
