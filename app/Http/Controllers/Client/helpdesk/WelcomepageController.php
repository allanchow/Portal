<?php

namespace App\Http\Controllers\Client\helpdesk;

// controllers
use App\Http\Controllers\Controller;
// models
use App\Model\helpdesk\Settings\System;
// classes
use Auth;
use Config;
use Redirect;

/**
 * OuthouseController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class WelcomepageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('board');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function get(System $note)
    {
        if (Config::get('database.install') == '%0%') {
            return Redirect::route('licence');
        }
        $notes = $note->get();
        foreach ($notes as $note) {
            $content = $note->content;
        }

        return view('themes.default1.client.guest-user.guest', compact('heading', 'content'));
    }

    public function index()
    {
        if(Auth::user()->role == 'admin' or Auth::user()->role == 'agent') {
            return Redirect::route('dashboard');
        } else {
            return Redirect::route('home');
        }
        $directory = base_path();
        if (file_exists($directory.DIRECTORY_SEPARATOR.'.env')) {
            return view('themes.default1.client.helpdesk.guest-user.index');
        } else {
            return Redirect::route('licence');
        }
    }
}
