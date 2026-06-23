<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * PortfolioController handles the retrieval and display of project images.
 */
class PortfolioController extends Controller
{
    /**
     * Display the portfolio index page with random project images.
     * * @return \Illuminate\View\View
     */
    public function index()
    {
        // Path to the directory: public/portfolio
        $directory = public_path('portfolio');
        $images = [];

        // Check if the directory exists
        if (File::exists($directory)) {
            // Retrieve all files from the directory
            $files = File::files($directory);

            foreach ($files as $file) {
                // Filter by allowed image extensions
                if (in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
                    $images[] = $file->getFilename();
                }
            }

            // Shuffle the array to randomize display order
            shuffle($images);
        }

        // Pass the list of filenames to the view
        return view('finishedProjects', compact('images'));
    }
}
