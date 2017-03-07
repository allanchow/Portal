<?php

namespace App\Http\Controllers\Client\helpdesk;

// controllers
use App\Http\Controllers\Controller;
// models
use App\User;
// classes
use Auth;
use DB;
use Exception;
use View;

/**
 * DashboardController
 * This controlleris used to fetch dashboard in the agent panel.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     * constructor to check
     * 1. authentication
     * 2. user roles
     * 3. roles must be agent.
     *
     * @return void
     */
    public function __construct()
    {
        // checking for authentication
        $this->middleware('auth');
        // checking if the role is user
        //$this->middleware('role.user');
    }

    /**
     * Get the dashboard page.
     *
     * @return type view
     */
    public function index()
    {
        if(Auth::user()->role == "agent" or Auth::user()->role == "admin"){
            return \Redirect::route('dashboard');
        }
        try {
            return View::make('themes.default1.client.helpdesk.dashboard');
        } catch (Exception $e) {
            return View::make('themes.default1.client.helpdesk.dashboard');
        }
    }
}
