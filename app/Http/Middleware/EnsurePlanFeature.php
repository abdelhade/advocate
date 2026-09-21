<?php

namespace App\Http\Middleware;

use App\Services\PlanLimitService;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanFeature
{
    public function __construct(private PlanLimitService $plans)
    {
    }

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = app(TenantContext::class)->get()
            ?? $request->user()?->currentTenant();

        if (! $tenant) {
            abort(403);
        }

        if ($this->plans->hasFeature($tenant, $feature)) {
            return $next($request);
        }

        $labels = [
            'tasks' => 'إدارة المهام',
            'billing' => 'الفوترة والأتعاب والمصروفات',
            'documents' => 'إدارة المستندات والتوثيق',
            'portal' => 'بوابة الموكلين',
        ];

        $label = $labels[$feature] ?? $feature;
        $message = "ميزة «{$label}» غير متاحة في خطتك الحالية. يرجى الترقية للوصول إليها.";

        if ($request->expectsJson() || $request->header('X-Inertia')) {
            return redirect()
                ->route('dashboard')
                ->with('error', $message);
        }

        return redirect()
            ->route('dashboard')
            ->with('error', $message);
    }
}
