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

            // Named routes keep the LAST registration — bind APP_URL host last
            // so route()/Ziggy don't resolve to 127.0.0.1 in production.
            $appHost = strtolower((string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: ''));
            if ($appHost !== '') {
                $centralDomains = array_values(array_unique([
                    ...array_filter($centralDomains, fn (string $d) => strtolower($d) !== $appHost),
                    $appHost,
                ]));
            }

            foreach ($centralDomains as $domain) {
                Route::middleware('web')
                    ->domain($domain)
                    ->group(base_path('routes/web.php'));
            }

            foreach (TenantUrl::availableDomains() as $baseDomain) {
                Route::middleware('web')
                    ->domain('{tenant_slug}.'.$baseDomain)
                    ->group(base_path('routes/tenant.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
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
