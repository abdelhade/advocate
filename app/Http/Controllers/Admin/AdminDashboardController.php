<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Stancl\Tenancy\Database\Models\Domain;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('domains')->latest()->get()->map(function ($tenant) {
            return [
                'id' => $tenant->id,
                'created_at' => $tenant->created_at->format('Y-m-d H:i'),
                'domains' => $tenant->domains->pluck('domain')->toArray(),
            ];
        });

        $stats = [
            'total_tenants' => Tenant::count(),
            'total_domains' => Domain::count(),
            'total_admins' => Admin::count(),
        ];

        // Recent tenants (last 5)
        $recentTenants = Tenant::with('domains')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'domains' => $tenant->domains->pluck('domain')->toArray(),
                    'created_at' => $tenant->created_at->format('Y-m-d H:i'),
                    'created_at_human' => $tenant->created_at->diffForHumans(),
                ];
            });

        // Monthly registration chart data (last 6 months)
        $chartData = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = Carbon::now()->subMonths($monthsAgo);
            return [
                'month' => $date->translatedFormat('M Y'),
                'count' => Tenant::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        })->values();

        return Inertia::render('Admin/Dashboard', [
            'tenants' => $tenants,
            'stats' => $stats,
            'recentTenants' => $recentTenants,
            'chartData' => $chartData,
        ]);
    }
}
