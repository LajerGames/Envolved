<?php

namespace App\Common;
 
class HandleSettings {

    /**
     * Whenever a story is created, we'll imbue it with some default settings - those will be returned here.
     * 
     * @param  string $type (editor or story)
     * @return array
     */
    public function GenerateDefaultSettings($type) {
        switch($type) {
            case 'editor' :
                return $this->GenerateDefaultEditorSettings();
                break;
            case 'story' :
                return $this->GenerateDefaultStorySettings();
                break;
        }
    }

    /**
     * Generates default settings for the story-editor
     * 
     * @return array
     */
    private function GenerateDefaultEditorSettings() {

        return [
            'tabs' => $this->GeneratsTabsArray([
                ['name' => 'Main', 'description' => 'Main'],
                ['name' => 'Idle', 'description' => 'Idle']
            ]),
            /** Phone */
            'phone_ring_patience' => 30, // sec
            'phone_time_between_call_logs_pregame' => 2, // min
            /** Texts */
            'text_time_before_read' => 10, // min
            'text_time_to_read' => 200, // wpm
            'text_time_to_reply' => 150, // cpm
            'text_time_between_texts_prestory' => 2, // min
            /** Photos */
            'photos_time_between_photos' => 10 // min
        ];

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
    public function GetSettings(\App\Story $story,  $type) {
        $settings = '';
        switch($type) {
            case 'editor' :
                $settings = $this->GetEditorSettings($story);
                break;
            case 'story' :
                $settings = $this->GetStorySettings($story);
                break;
        }

        return json_decode($settings);
    }

    /**
     * Get editor settings as JSON
     * 
     * @param Story $story
     * @return JSON
     */
    public function GetEditorSettings(\App\Story $story) {
        return $story->settings->editor_settings;
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