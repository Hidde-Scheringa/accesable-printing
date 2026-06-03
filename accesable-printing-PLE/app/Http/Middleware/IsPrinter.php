<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsPrinter
{

    public function handle($request, \Closure $next)
    {
        // Check of de gebruiker is ingelogd EN of hij de printer-rol (1) heeft
        if (auth()->check() && auth()->user()->isPrinter()) {
            return $next($request);
        }

        // Zo niet? Stuur ze terug naar het dashboard met een melding
        return redirect('/dashboard')->with('error', 'No acces');
    }
}
