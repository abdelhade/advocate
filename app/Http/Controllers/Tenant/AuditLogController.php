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

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        return Inertia::render('Tenant/AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['action', 'entity_type']),
        ]);
    }
}
