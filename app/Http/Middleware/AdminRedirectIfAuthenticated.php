<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// YAHAN CLASS KA NAAM FILE KE NAAM SE MATCH HONA CHAHIYE
class AdminRedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string $guard = 'admin'): Response
    {
        // Agar admin pehle se login hai toh usko admin dashboard par bhejo
        if (Auth::guard($guard)->check()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
