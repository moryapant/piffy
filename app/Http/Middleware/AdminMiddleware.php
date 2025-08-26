<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->is_admin) {
            return Redirect::route('dashboard')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
