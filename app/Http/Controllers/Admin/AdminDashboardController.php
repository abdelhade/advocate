<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\LegalCase;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Support\FastTableStats;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('admin_dashboard_stats', 600, function () {
            $tenantBreakdown = Tenant::query()
                ->toBase()
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended
                ")
                ->first();

            return [
                'total_tenants' => (int) ($tenantBreakdown->total ?? 0),
                'active_tenants' => (int) ($tenantBreakdown->active ?? 0),
                'suspended_tenants' => (int) ($tenantBreakdown->suspended ?? 0),
                'total_users' => (int) User::where('is_super_admin', false)->count(),
                'total_clients' => FastTableStats::approximateCount(
                    'clients',
                    fn () => Client::withoutGlobalScopes()->count()
                ),
                'total_cases' => FastTableStats::approximateCount(
                    'legal_cases',
                    fn () => LegalCase::withoutGlobalScopes()->count()
                ),
                'total_revenue' => (float) Cache::remember(
                    'admin_dashboard_revenue',
                    1800,
                    fn () => Payment::withoutGlobalScopes()->sum('amount') ?? 0
                ),
                'stats_approximate' => true,
            ];
        });

        $recentTenants = Cache::remember('admin_dashboard_recent_tenants', 120, function () {
            return Tenant::query()
                ->with(['users' => function ($q) {
                    $q->wherePivot('is_owner', true)->select('users.id', 'users.name');
                }])
                ->withCount('users')
                ->latest()
                ->take(6)
                ->get()
                ->map(function ($tenant) {
                    $owner = $tenant->users->first();

                    return [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'slug' => $tenant->slug,
                        'email' => $tenant->email,
                        'phone' => $tenant->phone ?? '-',
                        'owner_name' => $owner?->name ?? 'غير محدد',
                        'status' => $tenant->status,
                        'users_count' => $tenant->users_count,
                        'created_at' => $tenant->created_at->format('Y-m-d H:i'),
                        'created_at_human' => $tenant->created_at->diffForHumans(),
                    ];
                });
        });

        $chartData = Cache::remember('admin_dashboard_chart', 600, function () {
            $start = Carbon::now()->subMonths(5)->startOfMonth();

            $rows = Tenant::query()
                ->toBase()
                ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, COUNT(*) as c')
                ->where('created_at', '>=', $start)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->get()
                ->keyBy(fn ($r) => sprintf('%04d-%02d', $r->y, $r->m));

            return collect(range(5, 0))->map(function ($monthsAgo) use ($rows) {
                $date = Carbon::now()->subMonths($monthsAgo);
                $key = $date->format('Y-m');

                return [
                    'month' => $date->translatedFormat('M Y'),
                    'count' => (int) ($rows[$key]->c ?? 0),
                ];
            })->values();
        });

        $recentAuditLogs = AuditLog::query()
            ->with([
                'user:id,name',
                'tenant:id,name',
            ])
            ->latest('id')
            ->take(8)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'entity_type' => class_basename($log->entity_type),
                    'user_name' => $log->user?->name ?? 'النظام',
                    'tenant_name' => $log->tenant?->name ?? 'المنصة المركزية',
                    'ip_address' => $log->ip_address ?? '127.0.0.1',
                    'created_at_human' => $log->created_at?->diffForHumans(),
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentTenants' => $recentTenants,
            'chartData' => $chartData,
            'recentAuditLogs' => $recentAuditLogs,
        ]);
    }
}
