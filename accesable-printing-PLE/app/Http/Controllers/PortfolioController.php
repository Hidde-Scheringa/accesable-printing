<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PortfolioController extends Controller
{
    public function index()
    {
        // Pad naar de map: public/portfolio
        $directory = public_path('portfolio');
        $images = [];

        // Controleer of de map bestaat
        if (File::exists($directory)) {
            // Haal alle bestanden op uit de map
            $files = File::files($directory);

            foreach ($files as $file) {
                // Filter op toegestane bestandstypes
                if (in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
                    $images[] = $file->getFilename();
                }
            }

            // Zet de afbeeldingen in een willekeurige volgorde
            shuffle($images);
        }

        // Stuur de lijst met bestandsnamen door naar de view
        return view('finishedProjects', compact('images'));
    }
}
