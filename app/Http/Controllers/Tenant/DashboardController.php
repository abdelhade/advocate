<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CourtSession;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\LegalCase;
use App\Models\Payment;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $totalInvoiced = Invoice::where('tenant_id', $tenantId)->sum('total_amount');
        $totalCollected = Payment::where('tenant_id', $tenantId)->sum('amount');
        $totalExpenses = Expense::where('tenant_id', $tenantId)->sum('amount');
        $totalUnpaid = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['draft', 'posted', 'partially_paid'])
            ->sum(DB::raw('total_amount - paid_amount'));

        $stats = [
            'total_clients' => Client::where('tenant_id', $tenantId)->count(),
            'active_cases' => LegalCase::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'upcoming_sessions' => CourtSession::whereHas('case', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->where('session_date', '>=', Carbon::today())->count(),
            'pending_tasks' => Task::where('tenant_id', $tenantId)->whereIn('status', ['pending', 'in_progress'])->count(),
            'total_invoiced' => $totalInvoiced,
            'total_collected' => $totalCollected,
            'total_unpaid' => $totalUnpaid,
            'total_expenses' => $totalExpenses,
            'net_revenue' => $totalCollected - $totalExpenses,
        ];

        $upcomingSessions = CourtSession::whereHas('case', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
            ->with('case')
            ->where('session_date', '>=', Carbon::today())
            ->orderBy('session_date', 'asc')
            ->take(5)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'case_title' => $session->case?->title,
                    'case_number' => $session->case?->case_number,
                    'session_date' => $session->session_date->format('Y-m-d H:i'),
                    'court_name' => $session->case?->court_name,
                ];
            });

        $recentCases = LegalCase::where('tenant_id', $tenantId)
            ->with('client')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($case) {
                return [
                    'id' => $case->id,
                    'title' => $case->title,
                    'case_number' => $case->case_number,
                    'client_name' => $case->client?->name,
                    'status' => $case->status,
                    'created_at' => $case->created_at->format('Y-m-d'),
                ];
            });

        $pendingTasks = Task::where('tenant_id', $tenantId)
            ->with(['case:id,title', 'assignee:id,name'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        return Inertia::render('Tenant/Dashboard', [
            'stats' => $stats,
            'upcomingSessions' => $upcomingSessions,
            'recentCases' => $recentCases,
            'pendingTasks' => $pendingTasks,
        ]);
    }
}
