<?php

namespace App\Http\Controllers;

use App\Models\Request as PrintRequest;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    /**
     * Toon het dashboard voor de printer.
     */
    public function index()
    {
        // We halen alle aanvragen op.
        // Zorg dat in je Model (App\Models\Request) de kolom 'stl_files' op 'array' staat gecast.
        $allRequests = PrintRequest::with('user')->latest()->get();

        return view('printer.dashboard', compact('allRequests'));
    }

    /**
     * De downloadZip functie hebben we niet meer nodig als we losse knoppen gebruiken,
     * maar je kunt hem laten staan voor het geval je hem later toch wilt fixen.
     */
}
