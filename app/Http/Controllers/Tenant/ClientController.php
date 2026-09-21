<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Client::class);

        $sortable = ['name', 'phone', 'created_at', 'type'];
        $sort = in_array($request->input('sort'), $sortable, true)
            ? $request->input('sort')
            : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $query = Client::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('national_id_or_cr', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $clients = $query->orderBy($sort, $direction)->paginate(25)->through(function ($client) {
            return [
                'id' => $client->id,
                'type' => $client->type,
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'national_id_or_cr' => $client->national_id_or_cr,
                'created_at' => $client->created_at->format('Y-m-d'),
            ];
        });

        $typeCounts = Client::query()
            ->selectRaw('type, count(*) as aggregate')
            ->groupBy('type')
            ->pluck('aggregate', 'type');

        $stats = [
            'total' => (int) $typeCounts->sum(),
            'individual' => (int) ($typeCounts['individual'] ?? 0),
            'company' => (int) ($typeCounts['company'] ?? 0),
            'organization' => (int) ($typeCounts['organization'] ?? 0),
        ];

        return Inertia::render('Tenant/Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only('search', 'type', 'sort', 'direction'),
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Client::class);

        return Inertia::render('Tenant/Clients/Create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Client::class);

        $validated = $request->validate([
            'type' => ['nullable', 'in:individual,company,organization'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'national_id_or_cr' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ], [
            'name.required' => 'اسم الموكل مطلوب.',
            'email.email' => 'يجب إدخال بريد إلكتروني صحيح.',
        ]);

        $validated['type'] = $validated['type'] ?? 'individual';
        $validated['national_id_or_cr'] = $validated['national_id_or_cr'] ?? $request->input('national_id');
        unset($validated['national_id']);

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'تم إضافة الموكل بنجاح.');
    }

    public function show(Client $client)
    {
        Gate::authorize('view', $client);

        $client->load('cases');

        return Inertia::render('Tenant/Clients/Show', [
            'client' => [
                'id' => $client->id,
                'type' => $client->type,
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'national_id_or_cr' => $client->national_id_or_cr,
                'address' => $client->address,
                'notes' => $client->notes,
                'created_at' => $client->created_at->format('Y-m-d'),
                'legal_cases' => $client->cases->map(function ($case) {
                    return [
                        'id' => $case->id,
                        'case_number' => $case->case_number,
                        'title' => $case->title,
                        'status' => $case->status,
                        'created_at' => $case->created_at->format('Y-m-d'),
                    ];
                }),
            ],
        ]);
    }

    public function edit(Client $client)
    {
        Gate::authorize('update', $client);

        return Inertia::render('Tenant/Clients/Edit', [
            'client' => [
                'id' => $client->id,
                'type' => $client->type,
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'national_id_or_cr' => $client->national_id_or_cr,
                'address' => $client->address,
                'notes' => $client->notes,
            ],
        ]);
    }

    public function update(Request $request, Client $client)
    {
        Gate::authorize('update', $client);

        $validated = $request->validate([
            'type' => ['nullable', 'in:individual,company,organization'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'national_id_or_cr' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ], [
            'name.required' => 'اسم الموكل مطلوب.',
            'email.email' => 'يجب إدخال بريد إلكتروني صحيح.',
        ]);

        $validated['type'] = $validated['type'] ?? $client->type ?? 'individual';
        $validated['national_id_or_cr'] = $validated['national_id_or_cr'] ?? $request->input('national_id') ?? $client->national_id_or_cr;
        unset($validated['national_id']);

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'تم تحديث بيانات الموكل بنجاح.');
    }

    public function destroy(Client $client)
    {
        Gate::authorize('delete', $client);

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'تم نقل الموكل إلى سلة المهملات بنجاح.');
    }
}
