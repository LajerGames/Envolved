<?php

namespace App\Common;
 
use App\PhoneNumber;

class HandleSettings {

    /**
     * Whenever a story or a character is created, we'll imbue it with some default settings - those will be returned here.
     * 
     * @param  string $type (editor, character or story)
     * @return array
     */
    public function GenerateDefaultSettings($type, $exclude = []) {
        switch($type) {
            case 'editor' :
            case 'character' :
                return $this->GenerateDefaultEditorSettings($exclude);
                break;
            case 'story' :
                return $this->GenerateDefaultStorySettings($exclude);
                break;
        }
    }

    /**
     * Generates default settings for the story-editor
     * 
     * @return array
     */
    private function GenerateDefaultEditorSettings($exclude) {

        $return = [];

        # Tabs
        if(!in_array('tabs', $exclude)) {
            $return['tabs'] = $this->GenerateTabsArray([
                ['name' => 'Main', 'description' => 'Main'],
                ['name' => 'Idle', 'description' => 'Idle']
            ]);
        }

        /** Phone */

        # Phone ring patience
        if(!in_array('phone_ring_patience', $exclude)) {
            $return['phone_ring_patience'] = 30; // Sec
        }

        # Phone time between call logs pregame
        if(!in_array('phone_time_between_call_logs_pregame', $exclude)) {
            $return['phone_time_between_call_logs_pregame'] = 2; // min
        }

        /** Texts */

        # Text time before read
        if(!in_array('text_time_before_read', $exclude)) {
            $return['text_time_before_read'] = 10; // sec
        }

        # Text time to read
        if(!in_array('text_time_to_read', $exclude)) {
            $return['text_time_to_read'] = 200; // wpm
        }

        # Text time to reply
        if(!in_array('text_time_to_reply', $exclude)) {
            $return['text_time_to_reply'] = 150; // cpm
        }

        # Text time between texts prestory
        if(!in_array('text_time_between_texts_prestory', $exclude)) {
            $return['text_time_between_texts_prestory'] = 2; // npm
        }

        /** Photos */

        # Photos time between photos
        if(!in_array('photos_time_between_photos', $exclude)) {
            $return['photos_time_between_photos'] = 10; // npm
        }

        return $return;

    }

    public function GenerateTabsArray($tabs) {
        
        // Loop through the tabs and generate each entry
        $tabEntries = [];
        foreach($tabs as $id => $tab) {
            $uniqueID = uniqid();
            if(strlen($id) > 12) {
                // This is absolutely a uniqueid() use that ! 
                $uniqueID = $id;
            }
            $tabEntries[$uniqueID] = $this->GenerateTabArrayEntry($tab['name'], $tab['description']);
        }

        return $tabEntries;

    }

    private function GenerateTabArrayEntry($name, $description) {
        return [
            'name' => $name,
            'description' => $description
        ];
    }

    /**
     * Generates default settings for the story
     * 
     * @return array
     */
    private function GenerateDefaultStorySettings() {

        return [
            
        ];

    }

    /**
     * Get either editor or story settings
     * 
     * @param Story $story
     * @param string $type (editor or story)
     * @return array
     */
    public function GetSettings(\App\Story $story,  $type, $character = '', $storyPoint = '', $otherSettingModel = '') {
        $settings = '';
        switch($type) {
            case 'editor' :
            case 'character' :
                $settings = $this->GetEditorSettings($story, $character, $storyPoint);
                break;
            case 'story' :
                $settings = $this->GetStorySettings($story);
                break;
            case 'other' :

                // Did we receive a model with settings?
                if(
                    is_object($otherSettingModel)
                    && isset($otherSettingModel->settings)
                    && !empty($otherSettingModel->settings)
                ) {

                    $settings = $this->GetOtherSettings($otherSettingModel);

                }

                break;
        }

        return $settings;
    }

    public function GetOtherSettings($otherSettingModel) {

        // What kind of other settings are we looking for?
        if($otherSettingModel instanceof PhoneNumber) { // Did we receive a phone number?

            $phoneNumberSettings = json_decode($otherSettingModel->settings);

            // This ia phone number.
            $settings = new \stdClass();
            $settings->messagable = $this->DecideOnValue($phoneNumberSettings, 'messagable', 0);
            $settings->text_story_arch = $this->DecideOnValue($phoneNumberSettings, 'text_story_arch', 0);
            $settings->call_story_arch = $this->DecideOnValue($phoneNumberSettings, 'call_story_arch', 0);

        }

        return $settings;

    }

    /**
     * Get editor settings as JSON
     * 
     * @param Story $story
     * @return object
     */
    public function GetEditorSettings(\App\Story $story, $character = '', $storyPoint = '') {

        $settings = json_decode($story->settings->editor_settings);

        // If we have a character, then those settings take priority over story settings
        if($character instanceof \App\Character) {
            $characterSettings = json_decode($character->settings);

            # Phone Ring Patience
            $settings->phone_ring_patience = $this->DecideOnValue($characterSettings, 'phone_ring_patience', $settings->phone_ring_patience);
            
            # Text Time Before Read
            $settings->text_time_before_read = $this->DecideOnValue($characterSettings, 'text_time_before_read', $settings->text_time_before_read);

            # Text Time To Read
            $settings->text_time_to_read = $this->DecideOnValue($characterSettings, 'text_time_to_read', $settings->text_time_to_read);

            # Text Time To Reply
            $settings->text_time_to_reply = $this->DecideOnValue($characterSettings, 'text_time_to_reply', $settings->text_time_to_reply);

        }

        // If we have provided a StoryPoint - then that value takes priority over anything else
        if($storyPoint instanceof \App\StoryPoint) {
            $storyPointSettings = json_decode($storyPoint->instructions_json);

            # Phone Ring Patience
            $settings->phone_ring_patience = $this->DecideOnValue($storyPointSettings, 'phone_ring_patience', $settings->phone_ring_patience);
            
            # Text Time Before Read
            $settings->text_time_before_read = $this->DecideOnValue($storyPointSettings, 'text_time_before_read', $settings->text_time_before_read);

            # Text Time To Read
            $settings->text_time_to_read = $this->DecideOnValue($storyPointSettings, 'text_time_to_read', $settings->text_time_to_read);

            # Text Time To Reply
            $settings->text_time_to_reply = $this->DecideOnValue($storyPointSettings, 'text_time_to_reply', $settings->text_time_to_reply);

        }

        return $settings;
    }

    private function DecideOnValue($object, $property, $fallback) {
        return
            is_object($object)
            && !empty($property)
            && property_exists($object, $property)
            && !empty($object->{$property})
                ? $object->{$property}
                : $fallback;
    }

    /**
     * Get story settings as JSON
     * 
     * @param Story $story
     * @return JSON
     */
    public function GetStorySettings(\App\Story $story) {
        return $story->settings->story_settings;
    }
}