<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Story;
use App\Common\Permission;
use App\Common\HandleSettings;

class SettingsEditorsController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $story_id
     * @return \Illuminate\Http\Response
     */
    public function edit($story_id)
    {
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        $handleSettings = new HandleSettings();

        $settings = $handleSettings->GetSettings($story, 'editor');

        $tabs = '';
        foreach($settings->tabs as $id => $tab) {
            $tabs .= $this->MakeTabHtml($id, $tab->name, $tab->description);
        }

        $info = [
            'story' => $story,
            'tabs' => $tabs,
            'settings' => $settings
        ];

        return view('stories.settings.editor')->with('info', $info);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $story_id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $story_id)
    {
        $story = Story::find($story_id);

        // Check for access
        if(
            !Permission::CheckOwnership(auth()->user()->id, $story->user->id)
            || !Permission::CheckOwnership($story_id, $story->id)
        )
            return redirect('/stories/'.$story->id.'/characters')->with('error', 'Access denied');

        // Get story settings model
        $settingsModel = $story->settings;

        $handleSettings = new HandleSettings();

        // Put all settings, except a select few, into an array, those select few we'll handle manually
        $settings = $request->all();
        $settings['tabs'] = $handleSettings->GenerateTabsArray($settings['tab']);
        unset($settings['_token']);
        unset($settings['_method']);
        unset($settings['tab']);
        
        // Save the new settings
        $settingsModel->editor_settings = json_encode($settings);
        $settingsModel->save();

        return redirect('/stories/'.$story_id.'/editor_settings/edit')->with('success', 'Settings updated');
    }

    public function MakeTabHtml($id = '', $name = '', $description = '')
    {
        $uniqueID = empty($id) ? uniqid() : $id;

        return '
            <div class="form-group">
                <a href="javascript:void(0);" class="remove-link">Remove</a>
                <label for="tab['.$uniqueID.'][name]" class="block-elem">'.(empty($name) ? '&nbsp;' : $name).'</label>
                <input placeholder="Headline" name="tab['.$uniqueID.'][name]" type="text" value="'.$name.'" class="tabs-name" id="tab['.$uniqueID.'][name]" required />
                <input placeholder="Description" name="tab['.$uniqueID.'][description]" type="text" value="'.$description.'" class="tabs-description" />
            </div>
        ';
    }
}
