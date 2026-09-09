<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\LegalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CaseController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalCase::with('client')->latest();

        if ($search = $request->input('search')) {
            $query->where('case_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('case_type')) {
            $query->where('case_type', $type);
        }

        $cases = $query->paginate(15)->through(function ($case) {
            return [
                'id' => $case->id,
                'case_number' => $case->case_number,
                'title' => $case->title,
                'client_name' => $case->client->name,
                'status' => $case->status,
                'case_type' => $case->case_type,
                'filed_at' => $case->filed_at ? $case->filed_at->format('Y-m-d') : null,
            ];
        });

        return Inertia::render('Tenant/Cases/Index', [
            'cases' => $cases,
            'filters' => $request->only('search', 'status', 'case_type'),
        ]);
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);
        return Inertia::render('Tenant/Cases/Create', [
            'clients' => $clients,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'case_number' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'client_id' => ['required', 'exists:clients,id'],
            'case_type' => ['required', 'string', 'max:255'],
            'court' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,postponed,judged,closed'],
            'opponent_name' => ['nullable', 'string', 'max:255'],
            'opponent_lawyer' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'filed_at' => ['nullable', 'date'],
        ]);

        $validated['user_id'] = Auth::id();

        LegalCase::create($validated);

        return redirect()->route('cases.index')
            ->with('success', 'تم إضافة القضية بنجاح.');
    }

    public function show(LegalCase $case)
    {
        $case->load(['client', 'courtSessions' => function ($query) {
            $query->orderBy('session_date', 'asc');
        }, 'caseNotes.user', 'user']);

        return Inertia::render('Tenant/Cases/Show', [
            'case' => [
                'id' => $case->id,
                'case_number' => $case->case_number,
                'title' => $case->title,
                'client' => $case->client,
                'case_type' => $case->case_type,
                'court' => $case->court,
                'status' => $case->status,
                'opponent_name' => $case->opponent_name,
                'opponent_lawyer' => $case->opponent_lawyer,
                'description' => $case->description,
                'filed_at' => $case->filed_at ? $case->filed_at->format('Y-m-d') : null,
                'created_at' => $case->created_at->format('Y-m-d'),
                'lawyer_name' => $case->user->name,
                'sessions' => $case->courtSessions->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'session_date' => $session->session_date->format('Y-m-d'),
                        'session_time' => $session->session_time ? $session->session_time->format('H:i') : null,
                        'location' => $session->location,
                        'decision' => $session->decision,
                    ];
                }),
                'notes' => $case->caseNotes->map(function ($note) {
                    return [
                        'id' => $note->id,
                        'content' => $note->content,
                        'user_name' => $note->user->name,
                        'created_at' => $note->created_at->format('Y-m-d H:i'),
                    ];
                }),
            ],
        ]);
    }

    public function edit(LegalCase $case)
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);
        
        return Inertia::render('Tenant/Cases/Edit', [
            'case' => [
                'id' => $case->id,
                'case_number' => $case->case_number,
                'title' => $case->title,
                'client_id' => $case->client_id,
                'case_type' => $case->case_type,
                'court' => $case->court,
                'status' => $case->status,
                'opponent_name' => $case->opponent_name,
                'opponent_lawyer' => $case->opponent_lawyer,
                'description' => $case->description,
                'filed_at' => $case->filed_at ? $case->filed_at->format('Y-m-d') : null,
            ],
            'clients' => $clients,
        ]);
    }

    public function update(Request $request, LegalCase $case)
    {
        $validated = $request->validate([
            'case_number' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'client_id' => ['required', 'exists:clients,id'],
            'case_type' => ['required', 'string', 'max:255'],
            'court' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,postponed,judged,closed'],
            'opponent_name' => ['nullable', 'string', 'max:255'],
            'opponent_lawyer' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'filed_at' => ['nullable', 'date'],
        ]);

        $case->update($validated);

        return redirect()->route('cases.index')
            ->with('success', 'تم تحديث القضية بنجاح.');
    }

    public function destroy(LegalCase $case)
    {
        $case->delete();

        return redirect()->route('cases.index')
            ->with('success', 'تم حذف القضية بنجاح.');
    }

    public function addNote(Request $request, LegalCase $case)
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $case->caseNotes()->create([
            'content' => $validated['content'],
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'تم إضافة الملاحظة بنجاح.');
    }
}
