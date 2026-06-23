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
        // if you are logged in and no print expert you can proceed
        if (auth()->check() && !auth()->user()->isPrinter()) {
            return $next($request);
        }

        // are you a print expert then you are redirected to your own erea
        return redirect()->route('printer.dashboard');
    }
}
