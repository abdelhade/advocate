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

                $settings = $tenant->settings ?? [];
                if (!empty($settings['expires_at'])) {
                    $expiresAt = \Carbon\Carbon::parse($settings['expires_at']);
                    $isTrial = false;
                } elseif ($tenant->status === 'active') {
                    $expiresAt = $tenant->created_at ? $tenant->created_at->addYear() : null;
                    $isTrial = false;
                } else {
                    $expiresAt = $tenant->created_at ? $tenant->created_at->addDays(15) : null;
                    $isTrial = true;
                }

                $daysLeft = $expiresAt ? max(0, (int) ceil(now()->diffInSeconds($expiresAt, false) / 86400)) : 0;

                // Share active tenant & user's tenants list with Inertia UI
                Inertia::share([
                    'auth.active_tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'slug' => $tenant->slug,
                        'status' => $tenant->status,
                        'is_trial' => $isTrial,
                        'days_left' => $daysLeft,
                        'start_date' => $tenant->created_at ? $tenant->created_at->format('Y-m-d') : null,
                        'expires_at' => $expiresAt ? $expiresAt->format('Y-m-d') : null,
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
