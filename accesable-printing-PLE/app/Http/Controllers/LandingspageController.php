<?php

namespace App\Http\Controllers;

use App\Models\Request;


class LandingspageController extends Controller
{
    public function index()
    {
        // collect the 5 newest requests that have a stl file
        $recentRequests = Request::whereNotNull('stl_files')
            ->latest()
            ->take(5)
            ->get();

        return view('welcome', compact('recentRequests'));
    }
}
