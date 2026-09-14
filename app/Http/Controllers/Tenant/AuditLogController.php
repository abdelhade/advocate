<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $query = AuditLog::where('tenant_id', $tenantId)
            ->with(['user:id,name,email']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('entity_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->latest('created_at')->paginate(25)->withQueryString();

        $entityTypesMap = [
            'App\Models\Client' => '👤 الموكلين',
            'App\Models\LegalCase' => '⚖️ القضايا',
            'App\Models\CourtSession' => '📅 الجلسات القضائية',
            'App\Models\Document' => '📄 المستندات والوثائق',
            'App\Models\Invoice' => '📜 الفواتير والأتعاب',
            'App\Models\Payment' => '💳 سندات القبض',
            'App\Models\Expense' => '💸 المصروفات',
            'App\Models\Task' => '📋 المهام الأسبوعية',
            'App\Models\User' => '👨‍⚖️ أعضاء الفريق',
        ];

        return Inertia::render('Tenant/AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['action', 'entity_type', 'search']),
            'entityTypesMap' => $entityTypesMap,
        ]);
    }
}
