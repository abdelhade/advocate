<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\LegalCase;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Platform Core Metrics
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'suspended_tenants' => Tenant::where('status', 'suspended')->count(),
            'total_users' => User::where('is_super_admin', false)->count(),
            'total_clients' => Client::withoutGlobalScopes()->count(),
            'total_cases' => LegalCase::withoutGlobalScopes()->count(),
            'total_revenue' => Payment::withoutGlobalScopes()->sum('amount'),
        ];

        // Recent Registered Offices (Tenants)
        $recentTenants = Tenant::with('users')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($tenant) {
                $owner = $tenant->users->firstWhere('pivot.is_owner', true);
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone ?? '-',
                    'owner_name' => $owner?->name ?? 'غير محدد',
                    'status' => $tenant->status,
                    'users_count' => $tenant->users->count(),
                    'created_at' => $tenant->created_at->format('Y-m-d H:i'),
                    'created_at_human' => $tenant->created_at->diffForHumans(),
                ];
            });

        // Monthly Office Registration Chart Data (last 6 months)
        $chartData = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = Carbon::now()->subMonths($monthsAgo);
            return [
                'month' => $date->translatedFormat('M Y'),
                'count' => Tenant::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        })->values();

        // System Audit Security Feed
        $recentAuditLogs = AuditLog::with(['user', 'tenant'])
            ->latest()
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
                    'created_at_human' => $log->created_at->diffForHumans(),
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
