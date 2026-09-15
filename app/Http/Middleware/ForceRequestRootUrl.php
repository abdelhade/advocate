<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ForceRequestRootUrl
{
    /**
     * Keep generated URLs (redirects, Ziggy, route()) on the current host
     * instead of APP_URL when it points at 127.0.0.1 / localhost.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $root = $request->getSchemeAndHttpHost();

        if ($root !== '') {
            URL::forceRootUrl($root);
        }

        if ($request->isSecure()) {
            URL::forceScheme('https');
        }

        return $next($request);
    }
}
