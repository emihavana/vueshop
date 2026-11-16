<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // $request->user() is the logged-in user, or null for guests.
        // abort(403) stops the request and shows a "Forbidden" page.
        if (! $request->user()?->is_admin) {
            abort(403);
        }

        return $next($request);   // let the request continue to the controller
    }
}