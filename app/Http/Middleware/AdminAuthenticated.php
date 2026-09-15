<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('admin')->check()) {
            // Relative path — never bounce to APP_URL host (e.g. 127.0.0.1)
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
