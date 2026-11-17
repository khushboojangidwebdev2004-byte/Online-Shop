<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Agar route URL me "admin" hai toh admin login pe bhejo
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            // YAHI MAIN CHANGE HAI: Normal user ko account.login par bhejna hai
            return route('account.login');
        }

        return null;
    }
}
