<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsCustomer
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Als je ingelogd bent en GEEN printer bent, mag je door
        if (auth()->check() && !auth()->user()->isPrinter()) {
            return $next($request);
        }

        // Ben je wel een printer? Dan word je naar jouw eigen dashboard gestuurd
        return redirect()->route('printer.dashboard');
    }
}
