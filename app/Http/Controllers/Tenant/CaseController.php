<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CaseType;
use App\Models\LegalCase;
use App\Services\PlanLimitService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CaseController extends Controller
{
    public function __construct(private PlanLimitService $planLimits)
    {
    }
    public function index(Request $request)
    {
        Gate::authorize('viewAny', LegalCase::class);

        $sortable = ['case_number', 'title', 'status', 'created_at'];
        $sort = in_array($request->input('sort'), $sortable, true)
            ? $request->input('sort')
            : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $query = LegalCase::with('client');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('case_type')) {
            $query->where('case_type', $type);
        }

        $cases = $query->orderBy($sort, $direction)->paginate(25)->through(function ($case) {
            return [
                'id' => $case->id,
                'case_number' => $case->case_number,
                'title' => $case->title,
                'client_name' => $case->client?->name,
                'status' => $case->status,
                'case_type' => $case->case_type,
                'court_name' => $case->court_name,
                'created_at' => $case->created_at->format('Y-m-d'),
            ];
        });

        $statusCounts = LegalCase::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stats = [
            'total' => (int) $statusCounts->sum(),
            'active' => (int) ($statusCounts['active'] ?? 0),
            'postponed' => (int) ($statusCounts['postponed'] ?? $statusCounts['suspended'] ?? 0),
            'judged' => (int) ($statusCounts['judged'] ?? (($statusCounts['won'] ?? 0) + ($statusCounts['lost'] ?? 0))),
            'closed' => (int) ($statusCounts['closed'] ?? 0),
        ];

        return Inertia::render('Tenant/Cases/Index', [
            'cases' => $cases,
            'filters' => $request->only('search', 'status', 'case_type', 'sort', 'direction'),
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        Gate::authorize('create', LegalCase::class);

        $clients = Client::orderBy('name')->get(['id', 'name']);
        $tenantId = app(TenantContext::class)->id();
        $caseTypes = CaseType::where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name', 'color']);
        return Inertia::render('Tenant/Cases/Create', [
            'clients' => $clients,
            'caseTypes' => $caseTypes,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', LegalCase::class);

        $tenant = app(TenantContext::class)->get() ?? auth()->user()?->currentTenant();
        abort_unless($tenant, 403);
        $this->planLimits->assertCanAddCase($tenant);

        $tenantId = $tenant->id;

        $validated = $request->validate([
            'case_number' => ['required', 'string', 'max:191'],
            'title' => ['required', 'string', 'max:255'],
            'client_id' => [
                'required',
                'exists:clients,id',
                function ($attribute, $value, $fail) use ($tenantId) {
                    if (!Client::where('id', $value)->where('tenant_id', $tenantId)->exists()) {
                        $fail('العميل المحدد لا ينتمي لهذا المكتب.');
                    }
                },
            ],
            'case_type' => ['nullable', 'string', 'max:100'],
            'court_name' => ['nullable', 'string', 'max:191'],
            'circuit' => ['nullable', 'string', 'max:191'],
            'status' => ['required', 'string', 'in:active,suspended,won,lost,closed'],
            'internal_number' => ['nullable', 'string', 'max:191'],
            'reminder_at' => ['nullable', 'date'],
            'opponent_name' => ['nullable', 'string', 'max:255'],
            'opponent_lawyer' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['primary_lawyer_id'] = Auth::id();

        $case = LegalCase::create($validated);

        if (!empty($validated['opponent_name'])) {
            $case->parties()->create([
                'name' => $validated['opponent_name'],
                'party_type' => 'opponent',
                'lawyer_name' => $validated['opponent_lawyer'] ?? null,
            ]);
        }

        return redirect()->route('cases.index')
            ->with('success', 'تم إضافة القضية بنجاح.');
    }

    public function show(LegalCase $case)
    {
        Gate::authorize('view', $case);

        $case->load(['client', 'sessions' => function ($query) {
            $query->orderBy('session_date', 'asc');
        }, 'parties', 'documents.uploader', 'primaryLawyer', 'tasks.assignee', 'invoices', 'expenses', 'media']);

        return Inertia::render('Tenant/Cases/Show', [
            'case' => [
                'id' => $case->id,
                'case_number' => $case->case_number,
                'internal_number' => $case->internal_number,
                'title' => $case->title,
                'client' => $case->client,
                'case_type' => $case->case_type,
                'court_name' => $case->court_name,
                'circuit' => $case->circuit,
                'status' => $case->status,
                'created_at' => $case->created_at->format('Y-m-d'),
                'lawyer_name' => $case->primaryLawyer?->name,
                'sessions' => $case->sessions->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'session_date' => $session->session_date->format('Y-m-d H:i'),
                        'status' => $session->status,
                        'requirements' => $session->requirements,
                        'results' => $session->results,
                        'next_session_date' => $session->next_session_date ? $session->next_session_date->format('Y-m-d') : null,
                    ];
                }),
                'parties' => $case->parties->map(function ($party) {
                    return [
                        'id' => $party->id,
                        'name' => $party->name,
                        'party_type' => $party->party_type,
                        'lawyer_name' => $party->lawyer_name,
                        'phone' => $party->phone,
                    ];
                }),
                'documents' => $case->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'mime_type' => $doc->mime_type,
                        'file_size' => round($doc->file_size / 1024, 2) . ' KB',
                        'uploader_name' => $doc->uploader?->name,
                        'created_at' => $doc->created_at->format('Y-m-d H:i'),
                    ];
                }),
                'attachments' => $case->getMedia('attachments')->map(function ($media) use ($case) {
                    return [
                        'id' => $media->id,
                        'file_name' => $media->file_name,
                        'name' => $media->name,
                        'mime_type' => $media->mime_type,
                        'size' => $media->size,
                        'human_readable_size' => $media->human_readable_size,
                        'uploader_name' => $media->getCustomProperty('uploader_name', '—'),
                        'created_at' => $media->created_at->format('Y-m-d H:i'),
                        'download_url' => route('cases.attachments.download', [$case->id, $media->id]),
                        'is_image' => str_starts_with($media->mime_type, 'image/'),
                        'thumb_url' => str_starts_with($media->mime_type, 'image/') ? $media->getUrl('thumb') : null,
                    ];
                }),
                'tasks' => $case->tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'status' => $task->status,
                        'priority' => $task->priority,
                        'assignee_name' => $task->assignee?->name,
                        'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                    ];
                }),
                'invoices' => $case->invoices->map(function ($inv) {
                    return [
                        'id' => $inv->id,
                        'invoice_number' => $inv->invoice_number,
                        'total_amount' => $inv->total_amount,
                        'paid_amount' => $inv->paid_amount,
                        'status' => $inv->status,
                    ];
                }),
                'expenses' => $case->expenses->map(function ($exp) {
                    return [
                        'id' => $exp->id,
                        'category' => $exp->category,
                        'amount' => $exp->amount,
                        'expense_date' => $exp->expense_date->format('Y-m-d'),
                    ];
                }),
            ],
        ]);
    }

    public function edit(LegalCase $case)
    {
        Gate::authorize('update', $case);

        $clients = Client::orderBy('name')->get(['id', 'name']);
        $tenantId = app(TenantContext::class)->id();
        $caseTypes = CaseType::where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name', 'color']);
        
        return Inertia::render('Tenant/Cases/Edit', [
            'case' => [
                'id' => $case->id,
                'case_number' => $case->case_number,
                'internal_number' => $case->internal_number,
                'title' => $case->title,
                'client_id' => $case->client_id,
                'case_type' => $case->case_type,
                'court_name' => $case->court_name,
                'circuit' => $case->circuit,
                'status' => $case->status,
                'reminder_at' => $case->reminder_at?->format('Y-m-d\TH:i'),
            ],
            'clients' => $clients,
            'caseTypes' => $caseTypes,
        ]);
    }

    public function update(Request $request, LegalCase $case)
    {
        Gate::authorize('update', $case);

        $tenantId = app(TenantContext::class)->id();

        $validated = $request->validate([
            'case_number' => ['required', 'string', 'max:191'],
            'title' => ['required', 'string', 'max:255'],
            'client_id' => [
                'required',
                'exists:clients,id',
                function ($attribute, $value, $fail) use ($tenantId) {
                    if (!Client::where('id', $value)->where('tenant_id', $tenantId)->exists()) {
                        $fail('العميل المحدد لا ينتمي لهذا المكتب.');
                    }
                },
            ],
            'case_type' => ['nullable', 'string', 'max:100'],
            'court_name' => ['nullable', 'string', 'max:191'],
            'circuit' => ['nullable', 'string', 'max:191'],
            'status' => ['required', 'string', 'in:active,suspended,won,lost,closed'],
            'internal_number' => ['nullable', 'string', 'max:191'],
            'reminder_at' => ['nullable', 'date'],
            'opponent_name' => ['nullable', 'string', 'max:255'],
            'opponent_lawyer' => ['nullable', 'string', 'max:255'],
        ]);

        $case->update($validated);

        if (!empty($validated['opponent_name'])) {
            $case->parties()->updateOrCreate(
                ['party_type' => 'opponent'],
                [
                    'name' => $validated['opponent_name'],
                    'lawyer_name' => $validated['opponent_lawyer'] ?? null,
                ]
            );
        }

        return redirect()->route('cases.index')
            ->with('success', 'تم تحديث القضية بنجاح.');
    }

    public function destroy(LegalCase $case)
    {
        Gate::authorize('delete', $case);

        $case->delete();

        return redirect()->route('cases.index')
            ->with('success', 'تم نقل القضية إلى سلة المهملات بنجاح.');
    }
}
