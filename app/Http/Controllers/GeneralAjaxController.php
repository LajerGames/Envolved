<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;

class GeneralAjaxController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Toggles the sidebar bool in session.
     *
     * @return void
     */
    public function ToggleSidebarSession() {

        $doShow = $_POST['data']['show'] == 'true' ? true : false; // Making sure it can only be a bool, casting it made it buggy :/
        
        \Session::put('ShowSidebar', $doShow);
    }

    public function AddNewsSection() {
        
        $newsItem = new NewsController();

        echo $newsItem->MakeNewsSection($_POST['data']['type']);

    }

    public function AddTab() {
        
        $settingsEditor = new SettingsEditorsController();

        echo $settingsEditor->MakeTabHtml();

    }

    public function GetStoryInfo() {

        $storyID = intval($_POST['data']['story_id']);

        $story = Story::find($storyID);

        echo json_encode([
            'title' => $story->title
        ]);
    }

    public function PrepareSaveModal() {

        $storyID = intval($_POST['data']['story_id']);
        $prechosenType = $_POST['data']['type'] == 'export' ? 'export' : 'backup';

        $story = Story::find($storyID);

        echo json_encode([
            'title' => $story->title,
            'type'  => $prechosenType
        ]);
    }
}
