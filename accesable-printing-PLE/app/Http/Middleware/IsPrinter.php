<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsPrinter
{

    public function handle($request, \Closure $next)
    {
        // check if the user is logged in and is he has a print expert role (1)
        if (auth()->check() && auth()->user()->isPrinter()) {
            return $next($request);
        }

        // if not then send them back to the user dashboard with a no acces
        return redirect('/dashboard')->with('error', 'No acces');
    }
}
