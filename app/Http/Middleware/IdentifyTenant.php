<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use App\Support\TenantUrl;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Resolve the active tenant from the request host (subdomain).
     * Central hosts (jalsateg.com, localhost) run without tenant context.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(TenantContext::class);
        $context->clear();

        $slug = $request->route('tenant_slug') ?: TenantUrl::slugFromHost($request->getHost());

        if (! $slug) {
            return $next($request);
        }

        if (TenantUrl::isReservedSlug($slug)) {
            abort(404);
        }

        $tenant = Tenant::query()
            ->where('slug', strtolower($slug))
            ->first();

        if (! $tenant || $tenant->status === 'suspended') {
            abort(404, 'المكتب غير موجود أو موقّف.');
        }

        $hostDomain = TenantUrl::domainFromHost($request->getHost());
        $tenantDomain = TenantUrl::domainFor($tenant);

        if (
            ! app()->environment(['local', 'testing'])
            && $hostDomain
            && $hostDomain !== $tenantDomain
        ) {
            abort(404, 'المكتب غير موجود على هذا النطاق.');
        }

        $context->set($tenant);
        session(['active_tenant_id' => $tenant->id]);
        URL::defaults(['tenant_slug' => $tenant->slug]);

        if ($request->route() && $request->route()->hasParameter('tenant_slug')) {
            $request->route()->forgetParameter('tenant_slug');
        }

        if (Auth::check()) {
            $user = Auth::user();

            $isMember = $user->is_super_admin || $user->tenants()
                ->where('tenants.id', $tenant->id)
                ->where('tenants.status', 'active')
                ->wherePivot('status', 'active')
                ->exists();

            if (! $isMember) {
                abort(403, 'غير مصرح لك بالوصول لهذا المكتب.');
            }

            $this->shareTenantWithInertia($user, $tenant);
        }

        return $next($request);
    }

    protected function shareTenantWithInertia($user, Tenant $tenant): void
    {
        $settings = $tenant->settings ?? [];
        if (! empty($settings['expires_at'])) {
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
                return $user->tenants()
                    ->select('tenants.id', 'tenants.name', 'tenants.slug')
                    ->get()
                    ->map(fn (Tenant $t) => [
                        'id' => $t->id,
                        'name' => $t->name,
                        'slug' => $t->slug,
                        'url' => TenantUrl::for($t, '/dashboard'),
                    ]);
            },
        ]);
    }
}
