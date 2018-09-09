<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
}
