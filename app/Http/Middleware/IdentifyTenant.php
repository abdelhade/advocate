<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(TenantContext::class);

        if (Auth::check()) {
            $user = Auth::user();

            // If super admin and visiting central admin routes, skip tenant scoping unless requested
            if ($user->is_super_admin && $request->routeIs('central.*')) {
                $context->clear();
                return $next($request);
            }

            $activeTenantId = session('active_tenant_id');
            $tenant = null;

            if ($activeTenantId) {
                // Verify user is member of this tenant
                $tenant = $user->tenants()->where('tenants.id', $activeTenantId)->first();
            }

            if (!$tenant) {
                // Default to first active tenant user belongs to
                $tenant = $user->tenants()->where('tenants.status', 'active')->first();
                if ($tenant) {
                    session(['active_tenant_id' => $tenant->id]);
                }
            }

            if ($tenant) {
                $context->set($tenant);

                // Share active tenant & user's tenants list with Inertia UI
                Inertia::share([
                    'auth.active_tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'slug' => $tenant->slug,
                    ],
                    'auth.user_tenants' => function () use ($user) {
                        return $user->tenants()->select('tenants.id', 'tenants.name', 'tenants.slug')->get();
                    },
                ]);
            }
        }

        return $next($request);
    }
}
