<?php

namespace App\Http\Controllers;

use App\Models\Request;


class LandingspageController extends Controller
{
    public function index()
    {
        // We halen de 5 nieuwste verzoeken op die een STL-bestand hebben
        $recentRequests = Request::whereNotNull('stl_files')
            ->latest()
            ->take(5)
            ->get();

        return view('welcome', compact('recentRequests'));
    }
}
