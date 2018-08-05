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
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Toggles the sidebar bool in session.
     *
     * @return void
     */
    public function ToggleSidebarSession()
    {
        session::put('ShowSidebar', (bool)$_POST['show']);
    }
}
