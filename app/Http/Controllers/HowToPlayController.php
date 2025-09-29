<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HowToPlayController extends Controller
{
    /**
     * Display the How to Play page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $realmlist = config('app.wow_realmlist', '185.175.16.107');
        
        return view('how-to-play', compact('realmlist'));
    }
}