<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CourtSession;
use App\Models\LegalCase;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_clients' => Client::count(),
            'active_cases' => LegalCase::where('status', 'active')->count(),
            'upcoming_sessions' => CourtSession::where('session_date', '>=', Carbon::today())->count(),
        ];

        $upcomingSessions = CourtSession::with('legalCase')
            ->where('session_date', '>=', Carbon::today())
            ->orderBy('session_date', 'asc')
            ->take(5)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'case_title' => $session->legalCase->title,
                    'case_number' => $session->legalCase->case_number,
                    'session_date' => $session->session_date->format('Y-m-d'),
                    'location' => $session->location,
                ];
            });

        $recentCases = LegalCase::with('client')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($case) {
                return [
                    'id' => $case->id,
                    'title' => $case->title,
                    'case_number' => $case->case_number,
                    'client_name' => $case->client->name,
                    'status' => $case->status,
                    'created_at' => $case->created_at->format('Y-m-d'),
                ];
            });

        return Inertia::render('Tenant/Dashboard', [
            'stats' => $stats,
            'upcomingSessions' => $upcomingSessions,
            'recentCases' => $recentCases,
        ]);
    }
}
