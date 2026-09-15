<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Public landing page - explains what Mwana is and how each role
     * (especially parents, who arrive with the least context) gets started.
     */
    public function index()
    {
        return view('home.index');
    }
}
