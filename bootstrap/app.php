<?php

use App\Support\TenantUrl;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            $centralDomains = array_values(array_unique(array_filter(
                config('tenancy.central_domains', ['localhost'])
            )));

            // Laravel keeps the FIRST registration for a given route name
            // (see RouteCollection::addLookups). Prefer APP_URL host among centrals,
            // and register tenant routes before central so shared names (if any)
            // resolve to the tenant portal — never 127.0.0.1.
            $appHost = strtolower((string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: ''));
            if ($appHost !== '') {
                $centralDomains = array_values(array_unique([
                    $appHost,
                    ...array_filter($centralDomains, fn (string $d) => strtolower($d) !== $appHost),
                ]));
            }

            foreach (TenantUrl::availableDomains() as $baseDomain) {
                Route::middleware('web')
                    ->domain('{tenant_slug}.'.$baseDomain)
                    ->group(base_path('routes/tenant.php'));
            }

            foreach ($centralDomains as $domain) {
                Route::middleware('web')
                    ->domain($domain)
                    ->group(base_path('routes/web.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB
        );

        $middleware->web(prepend: [
            \App\Http\Middleware\ForceRequestRootUrl::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\IdentifyTenant::class,
        ]);

        // auth may run before IdentifyTenant — build login URL from the host slug
        $middleware->redirectGuestsTo(function (Request $request) {
            return TenantUrl::loginUrl($request);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
